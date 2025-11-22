<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-customers')->only(['index', 'show']);
        $this->middleware('permission:create-customers')->only(['create', 'store']);
        $this->middleware('permission:edit-customers')->only(['edit', 'update']);
        $this->middleware('permission:delete-customers')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Customer::where('company_id', Auth::user()->company_id)
            ->with(['assignedUser:id,name', 'contacts' => function ($query) {
                $query->where('is_primary', true);
            }]);

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->withStatus($request->input('status'));
        }

        // Filter by type
        if ($request->filled('customer_type')) {
            $query->ofType($request->input('customer_type'));
        }

        // Filter by industry
        if ($request->filled('industry')) {
            $query->inIndustry($request->input('industry'));
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->assignedTo($request->input('assigned_to'));
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            if ($request->input('is_active') === 'true') {
                $query->active();
            }
        }

        // Sort
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $customers = $query->paginate($request->input('per_page', 15));

        // Get filter options
        $statuses = Customer::getStatuses();
        $types = Customer::getTypes();
        $industries = Customer::getIndustries();
        $sources = Customer::getSources();

        // Get team members for assignment filter
        $teamMembers = Auth::user()->company->users()->select('id', 'name')->get();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'customer_type' => $request->input('customer_type', ''),
                'industry' => $request->input('industry', ''),
                'assigned_to' => $request->input('assigned_to', ''),
                'is_active' => $request->input('is_active', 'true'),
                'sort_field' => $sortField,
                'sort_direction' => $sortDirection,
            ],
            'options' => [
                'statuses' => $statuses,
                'types' => $types,
                'industries' => $industries,
                'sources' => $sources,
                'teamMembers' => $teamMembers,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Customers/Create', [
            'options' => [
                'statuses' => Customer::getStatuses(),
                'types' => Customer::getTypes(),
                'industries' => Customer::getIndustries(),
                'sources' => Customer::getSources(),
                'teamMembers' => Auth::user()->company->users()->select('id', 'name')->get(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Customer Information
            'customer_type' => ['required', 'string', 'in:individual,company'],
            'name' => ['required_if:customer_type,individual', 'string', 'max:255'],
            'company_name' => ['required_if:customer_type,company', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'employees_count' => ['nullable', 'integer', 'min:0'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:lead,prospect,active,inactive,churned'],
            'source' => ['nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],

            // Primary Contact Information
            'contact.first_name' => ['required', 'string', 'max:255'],
            'contact.last_name' => ['required', 'string', 'max:255'],
            'contact.email' => ['nullable', 'email', 'max:255'],
            'contact.phone' => ['nullable', 'string', 'max:255'],
            'contact.mobile' => ['nullable', 'string', 'max:255'],
            'contact.job_title' => ['nullable', 'string', 'max:255'],
            'contact.department' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::beginTransaction();

            // Create the customer
            $customer = Customer::create([
                'company_id' => Auth::user()->company_id,
                'created_by' => Auth::id(),
                'customer_type' => $validated['customer_type'],
                'name' => $validated['name'] ?? $validated['company_name'],
                'company_name' => $validated['company_name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'website' => $validated['website'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'],
                'industry' => $validated['industry'],
                'tax_number' => $validated['tax_number'],
                'registration_number' => $validated['registration_number'],
                'employees_count' => $validated['employees_count'],
                'annual_revenue' => $validated['annual_revenue'],
                'status' => $validated['status'],
                'source' => $validated['source'],
                'assigned_to' => $validated['assigned_to'],
                'notes' => $validated['notes'],
                'is_active' => true,
            ]);

            // Create primary contact if provided
            if (!empty($validated['contact']['first_name'])) {
                CustomerContact::create([
                    'customer_id' => $customer->id,
                    'first_name' => $validated['contact']['first_name'],
                    'last_name' => $validated['contact']['last_name'],
                    'email' => $validated['contact']['email'],
                    'phone' => $validated['contact']['phone'],
                    'mobile' => $validated['contact']['mobile'],
                    'job_title' => $validated['contact']['job_title'],
                    'department' => $validated['contact']['department'],
                    'is_primary' => true,
                    'is_active' => true,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('customers.show', $customer->id)
                ->with('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create customer. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): Response
    {
        // Ensure user can only view their own company's customers
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $customer->load([
            'assignedUser:id,name,email',
            'creator:id,name',
            'contacts',
            'deals' => function ($query) {
                $query->latest()->limit(5);
            },
            'quotes' => function ($query) {
                $query->latest()->limit(5);
            },
            'invoices' => function ($query) {
                $query->latest()->limit(5);
            },
            'activities' => function ($query) {
                $query->with('user:id,name')->latest()->limit(10);
            }
        ]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): Response
    {
        // Ensure user can only edit their own company's customers
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $customer->load('contacts');

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'options' => [
                'statuses' => Customer::getStatuses(),
                'types' => Customer::getTypes(),
                'industries' => Customer::getIndustries(),
                'sources' => Customer::getSources(),
                'teamMembers' => Auth::user()->company->users()->select('id', 'name')->get(),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        // Ensure user can only update their own company's customers
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            // Customer Information
            'customer_type' => ['required', 'string', 'in:individual,company'],
            'name' => ['required_if:customer_type,individual', 'string', 'max:255'],
            'company_name' => ['required_if:customer_type,company', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'phone' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'employees_count' => ['nullable', 'integer', 'min:0'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:lead,prospect,active,inactive,churned'],
            'source' => ['nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
        ]);

        try {
            $customer->update([
                'customer_type' => $validated['customer_type'],
                'name' => $validated['name'] ?? $validated['company_name'],
                'company_name' => $validated['company_name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'website' => $validated['website'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'],
                'industry' => $validated['industry'],
                'tax_number' => $validated['tax_number'],
                'registration_number' => $validated['registration_number'],
                'employees_count' => $validated['employees_count'],
                'annual_revenue' => $validated['annual_revenue'],
                'status' => $validated['status'],
                'source' => $validated['source'],
                'assigned_to' => $validated['assigned_to'],
                'notes' => $validated['notes'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return redirect()
                ->route('customers.show', $customer->id)
                ->with('success', 'Customer updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update customer. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // Ensure user can only delete their own company's customers
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        try {
            // Soft delete by setting is_active to false
            $customer->update(['is_active' => false]);

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer deleted successfully.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete customer. Please try again.']);
        }
    }

    /**
     * Get customers for API/autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $customers = Customer::where('company_id', Auth::user()->company_id)
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('company_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'company_name', 'email', 'phone']);

        return response()->json($customers);
    }

    /**
     * Get customer statistics for dashboard.
     */
    public function stats(): JsonResponse
    {
        $stats = Customer::getDashboardStats();

        return response()->json($stats);
    }
}
