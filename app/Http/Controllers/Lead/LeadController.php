<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-leads')->only(['index', 'show', 'search', 'stats']);
        $this->middleware('permission:edit-leads')->only(['create', 'store', 'edit', 'update']);
        $this->middleware('permission:manage-leads')->only(['destroy', 'convert', 'bulkAction']);
    }

    /**
     * Display a listing of leads.
     */
    public function index(Request $request): Response
    {
        $query = Lead::where('company_id', Auth::user()->company_id)
            ->with(['owner:id,name,email', 'customer:id,display_name']);

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        } else {
            // Default to active leads
            $query->active();
        }

        // Filter by owner
        if ($request->filled('owner_id')) {
            $query->byOwner($request->input('owner_id'));
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->input('rating'));
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }

        // High priority filter
        if ($request->boolean('high_priority')) {
            $query->highPriority();
        }

        // Follow-up needed filter
        if ($request->boolean('needs_follow_up')) {
            $query->needingFollowUp();
        }

        // Sort
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $leads = $query->paginate($request->input('per_page', 15));

        // Get statistics
        $stats = [
            'total' => Lead::where('company_id', Auth::user()->company_id)->count(),
            'active' => Lead::where('company_id', Auth::user()->company_id)->active()->count(),
            'converted' => Lead::where('company_id', Auth::user()->company_id)->converted()->count(),
            'lost' => Lead::where('company_id', Auth::user()->company_id)->lost()->count(),
            'high_priority' => Lead::where('company_id', Auth::user()->company_id)->highPriority()->count(),
            'needs_follow_up' => Lead::where('company_id', Auth::user()->company_id)->needingFollowUp()->count(),
            'hot' => Lead::where('company_id', Auth::user()->company_id)->hot()->count(),
        ];

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'stats' => $stats,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'owner_id' => $request->input('owner_id', ''),
                'rating' => $request->input('rating', ''),
                'source' => $request->input('source', ''),
                'high_priority' => $request->boolean('high_priority'),
                'needs_follow_up' => $request->boolean('needs_follow_up'),
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
            'users' => User::where('company_id', Auth::user()->company_id)
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create(): Response
    {
        return Inertia::render('Leads/Create', [
            'users' => User::where('company_id', Auth::user()->company_id)
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'string', 'in:new,contacted,qualified,proposal,negotiation'],
            'source' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'employees' => ['nullable', 'integer', 'min:1'],
            'estimated_value' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'priority' => ['required', 'integer', 'between:1,5'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'user_id' => ['nullable', 'exists:users,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
        ]);

        try {
            DB::beginTransaction();

            $lead = Lead::create([
                'company_id' => Auth::user()->company_id,
                'user_id' => $validated['user_id'] ?: Auth::id(),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'mobile' => $validated['mobile'],
                'company_name' => $validated['company_name'],
                'job_title' => $validated['job_title'],
                'website' => $validated['website'],
                'description' => $validated['description'],
                'status' => $validated['status'],
                'source' => $validated['source'],
                'industry' => $validated['industry'],
                'employees' => $validated['employees'],
                'estimated_value' => $validated['estimated_value'],
                'currency' => $validated['currency'],
                'priority' => $validated['priority'],
                'follow_up_date' => $validated['follow_up_date'],
                'notes' => $validated['notes'],
                'tags' => $validated['tags'],
            ]);

            // Update lead score automatically
            $lead->updateScore();

            DB::commit();

            return redirect()
                ->route('leads.index')
                ->with('success', 'Lead created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create lead. Please try again.']);
        }
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead): Response
    {
        // Ensure user can only view leads from their company
        if ($lead->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $lead->load([
            'owner:id,name,email',
            'customer:id,display_name,customer_type',
            'convertedBy:id,name,email',
            'activities' => function ($query) {
                $query->with('user:id,name,email')
                    ->orderBy('created_at', 'desc')
                    ->limit(50);
            },
        ]);

        return Inertia::render('Leads/Show', [
            'lead' => $lead,
        ]);
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Lead $lead): Response
    {
        // Ensure user can only edit leads from their company
        if ($lead->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
            'users' => User::where('company_id', Auth::user()->company_id)
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        // Ensure user can only update leads from their company
        if ($lead->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:leads,email,' . $lead->id],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'string', 'in:new,contacted,qualified,proposal,negotiation,converted,lost,recycled'],
            'source' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'employees' => ['nullable', 'integer', 'min:1'],
            'estimated_value' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'priority' => ['required', 'integer', 'between:1,5'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'user_id' => ['nullable', 'exists:users,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
        ]);

        // Handle lost status
        if ($validated['status'] === 'lost') {
            $lostValidated = $request->validate([
                'lost_reason' => ['required', 'string', 'max:255'],
                'lost_details' => ['nullable', 'string', 'max:5000'],
            ]);
            $validated['lost_reason'] = $lostValidated['lost_reason'];
            $validated['lost_details'] = $lostValidated['lost_details'];
        } else {
            $validated['lost_reason'] = null;
            $validated['lost_details'] = null;
        }

        try {
            DB::beginTransaction();

            $lead->update($validated);

            // Update lead score if data changed
            if ($lead->wasChanged([
                'email', 'phone', 'company_name', 'job_title', 'website',
                'estimated_value', 'priority', 'industry', 'employees'
            ])) {
                $lead->updateScore();
            }

            DB::commit();

            return redirect()
                ->route('leads.show', $lead)
                ->with('success', 'Lead updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update lead. Please try again.']);
        }
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead)
    {
        // Ensure user can only delete leads from their company
        if ($lead->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        try {
            $lead->delete();

            return redirect()
                ->route('leads.index')
                ->with('success', 'Lead deleted successfully.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete lead. Please try again.']);
        }
    }

    /**
     * Convert lead to customer.
     */
    public function convert(Lead $lead): JsonResponse
    {
        // Ensure user can only convert leads from their company
        if ($lead->company_id !== Auth::user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Check if lead is already converted
        if ($lead->isConverted()) {
            return response()->json([
                'success' => false,
                'message' => 'Lead is already converted.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $customer = $lead->convertToCustomer(Auth::user());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lead converted to customer successfully.',
                'customer_id' => $customer->id,
                'customer_url' => route('customers.show', $customer),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to convert lead. Please try again.',
            ], 500);
        }
    }

    /**
     * Get leads for API/autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $leads = Lead::where('company_id', Auth::user()->company_id)
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('company_name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email', 'phone', 'company_name', 'status']);

        return response()->json($leads);
    }

    /**
     * Get lead statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Lead::where('company_id', Auth::user()->company_id)->count(),
            'new' => Lead::where('company_id', Auth::user()->company_id)->byStatus('new')->count(),
            'contacted' => Lead::where('company_id', Auth::user()->company_id)->byStatus('contacted')->count(),
            'qualified' => Lead::where('company_id', Auth::user()->company_id)->byStatus('qualified')->count(),
            'proposal' => Lead::where('company_id', Auth::user()->company_id)->byStatus('proposal')->count(),
            'negotiation' => Lead::where('company_id', Auth::user()->company_id)->byStatus('negotiation')->count(),
            'converted' => Lead::where('company_id', Auth::user()->company_id)->converted()->count(),
            'lost' => Lead::where('company_id', Auth::user()->company_id)->lost()->count(),
            'hot' => Lead::where('company_id', Auth::user()->company_id)->hot()->count(),
            'high_priority' => Lead::where('company_id', Auth::user()->company_id)->highPriority()->count(),
            'needs_follow_up' => Lead::where('company_id', Auth::user()->company_id)->needingFollowUp()->count(),
            'total_value' => Lead::where('company_id', Auth::user()->company_id)
                ->whereNotNull('estimated_value')
                ->sum('estimated_value'),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk operations on leads.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:assign,status,delete,export'],
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['integer', 'exists:leads,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'string', 'in:new,contacted,qualified,proposal,negotiation,lost,recycled'],
        ]);

        try {
            $leadIds = $validated['lead_ids'];
            $action = $validated['action'];

            // Get leads that belong to current company
            $leads = Lead::whereIn('id', $leadIds)
                ->where('company_id', Auth::user()->company_id)
                ->get();

            $count = 0;

            switch ($action) {
                case 'assign':
                    if (!$validated['user_id']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'User ID is required for assignment.',
                        ], 400);
                    }
                    $leads->each->update(['user_id' => $validated['user_id']]);
                    $count = $leads->count();
                    $message = "{$count} leads assigned successfully.";
                    break;

                case 'status':
                    if (!$validated['status']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Status is required for status change.',
                        ], 400);
                    }
                    $leads->each->update(['status' => $validated['status']]);
                    $count = $leads->count();
                    $message = "{$count} leads status updated successfully.";
                    break;

                case 'delete':
                    $leads->each->delete();
                    $count = $leads->count();
                    $message = "{$count} leads deleted successfully.";
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