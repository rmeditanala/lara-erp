<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Models\CustomerContact;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-customers')->only(['index', 'show']);
        $this->middleware('permission:edit-customers')->only(['create', 'store', 'edit', 'update']);
        $this->middleware('permission:manage-customer-contacts')->only(['destroy', 'makePrimary']);
    }

    /**
     * Display a listing of contacts for a specific customer.
     */
    public function index(Request $request, Customer $customer): Response
    {
        // Ensure user can only view contacts for their own company's customers
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $query = $customer->contacts()
            ->with(['customer:id,display_name'])
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->input('is_active') === 'true');
        }

        // Filter by primary contact
        if ($request->filled('is_primary')) {
            $query->where('is_primary', $request->input('is_primary') === 'true');
        }

        $contacts = $query->paginate($request->input('per_page', 15));

        return Inertia::render('Contacts/Index', [
            'customer' => $customer,
            'contacts' => $contacts,
            'filters' => [
                'search' => $request->input('search', ''),
                'is_active' => $request->input('is_active', 'true'),
                'is_primary' => $request->input('is_primary', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create(Request $request): Response
    {
        $customerId = $request->input('customer_id');

        // Validate customer belongs to current company
        if ($customerId) {
            $customer = Customer::where('id', $customerId)
                ->where('company_id', Auth::user()->company_id)
                ->firstOrFail();
        }

        return Inertia::render('Contacts/Create', [
            'customer' => $customer ?? null,
        ]);
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['boolean'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        // Validate customer belongs to current company
        $customer = Customer::where('id', $validated['customer_id'])
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // If this contact is set as primary, remove primary status from other contacts
            if ($validated['is_primary']) {
                CustomerContact::where('customer_id', $validated['customer_id'])
                    ->update(['is_primary' => false]);
            }

            // Create the contact
            $contact = CustomerContact::create([
                'customer_id' => $validated['customer_id'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'mobile' => $validated['mobile'],
                'job_title' => $validated['job_title'],
                'department' => $validated['department'],
                'is_primary' => $validated['is_primary'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            return redirect()
                ->route('customers.show', $customer->id)
                ->with('success', 'Contact created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create contact. Please try again.']);
        }
    }

    /**
     * Display the specified contact.
     */
    public function show(Customer $customer, CustomerContact $contact): Response
    {
        // Ensure user can only view contacts for their own company's customers
        if ($customer->company_id !== Auth::user()->company_id || $contact->customer_id !== $customer->id) {
            abort(403);
        }

        $contact->load([
            'customer:id,display_name,customer_type,email,phone',
            'activities' => function ($query) {
                $query->with('user:id,name,email')
                    ->orderBy('created_at', 'desc')
                    ->limit(50);
            },
        ]);

        return Inertia::render('Contacts/Show', [
            'customer' => $customer,
            'contact' => $contact,
        ]);
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Customer $customer, CustomerContact $contact): Response
    {
        // Ensure user can only edit contacts for their own company's customers
        if ($customer->company_id !== Auth::user()->company_id || $contact->customer_id !== $customer->id) {
            abort(403);
        }

        return Inertia::render('Contacts/Edit', [
            'customer' => $customer,
            'contact' => $contact,
        ]);
    }

    /**
     * Update the specified contact in storage.
     */
    public function update(Request $request, Customer $customer, CustomerContact $contact)
    {
        // Ensure user can only update contacts for their own company's customers
        if ($customer->company_id !== Auth::user()->company_id || $contact->customer_id !== $customer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customer_contacts,email,' . $contact->id],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['boolean'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            DB::beginTransaction();

            // If this contact is set as primary, remove primary status from other contacts
            if ($validated['is_primary'] && !$contact->is_primary) {
                CustomerContact::where('customer_id', $customer->id)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }

            $contact->update($validated);

            DB::commit();

            return redirect()
                ->route('customers.show', $customer->id)
                ->with('success', 'Contact updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update contact. Please try again.']);
        }
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy(Customer $customer, CustomerContact $contact)
    {
        // Ensure user can only delete contacts for their own company's customers
        if ($customer->company_id !== Auth::user()->company_id || $contact->customer_id !== $customer->id) {
            abort(403);
        }

        try {
            // Soft delete by setting is_active to false
            $contact->update(['is_active' => false]);

            return redirect()
                ->route('customers.show', $customer->id)
                ->with('success', 'Contact deactivated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to deactivate contact. Please try again.']);
        }
    }

    /**
     * Make a contact the primary contact for the customer.
     */
    public function makePrimary(Customer $customer, CustomerContact $contact): JsonResponse
    {
        // Ensure user can manage contacts for their own company's customers
        if ($customer->company_id !== Auth::user()->company_id || $contact->customer_id !== $customer->id) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            // Remove primary status from all other contacts
            CustomerContact::where('customer_id', $customer->id)
                ->where('id', '!=', $contact->id)
                ->update(['is_primary' => false]);

            // Set this contact as primary
            $contact->update(['is_primary' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contact set as primary successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to set contact as primary.',
            ], 500);
        }
    }

    /**
     * Get contacts for API/autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $customerId = $request->input('customer_id');

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer ID is required.',
            ], 400);
        }

        // Validate customer belongs to current company
        $customer = Customer::where('id', $customerId)
            ->where('company_id', Auth::user()->company_id)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        $contacts = $customer->contacts()
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email', 'phone', 'mobile', 'job_title', 'is_primary']);

        return response()->json($contacts);
    }

    /**
     * Bulk operations on contacts.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:activate,delete,export'],
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer', 'exists:customer_contacts,id'],
        ]);

        try {
            $contactIds = $validated['contact_ids'];
            $action = $validated['action'];

            // Get contacts that belong to current company's customers
            $contacts = CustomerContact::whereIn('id', $contactIds)
                ->whereHas('customer', function ($query) {
                    $query->where('company_id', Auth::user()->company_id);
                })
                ->get();

            $count = 0;

            switch ($action) {
                case 'activate':
                    $contacts->each->update(['is_active' => true]);
                    $count = $contacts->count();
                    $message = "{$count} contacts activated successfully.";
                    break;

                case 'deactivate':
                    $contacts->each->update(['is_active' => false]);
                    $count = $contacts->count();
                    $message = "{$count} contacts deactivated successfully.";
                    break;

                case 'delete':
                    $contacts->each->update(['is_active' => false]);
                    $count = $contacts->count();
                    $message = "{$count} contacts deleted successfully.";
                    break;

                case 'export':
                    // TODO: Implement export functionality
                    return response()->json([
                        'success' => false,
                        'message' => 'Export functionality not yet implemented.',
                    ], 501);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $count,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed. Please try again.',
            ], 500);
        }
    }
}
