<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    /**
     * Display a listing of the opportunities.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $company = $user->company;

        $query = Opportunity::where('company_id', $company->id)
            ->with(['user:id,name,email', 'lead:id,first_name,last_name,company']);

        // Pipeline filter
        $pipeline = $request->get('pipeline', 'sales');
        $query->forPipeline($pipeline);

        // Status filters
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Stage filter
        if ($request->filled('stage')) {
            $query->where('stage', $request->get('stage'));
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        // User assignment filter
        if ($request->filled('user_id')) {
            if ($request->get('user_id') === 'unassigned') {
                $query->whereNull('user_id');
            } else {
                $query->where('user_id', $request->get('user_id'));
            }
        }

        // Amount range filter
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->get('amount_min'));
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->get('amount_max'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('account_name', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $opportunities = $query->paginate(15)->withQueryString();

        // Get filter options
        $users = User::where('company_id', $company->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $pipelineStages = Opportunity::getPipelineStages($pipeline);
        $stats = Opportunity::getPipelineStats($company->id, $pipeline);

        return Inertia::render('Opportunities/Index', [
            'opportunities' => $opportunities,
            'filters' => $request->only([
                'search', 'pipeline', 'status', 'stage', 'priority',
                'user_id', 'amount_min', 'amount_max', 'date_from', 'date_to',
                'sort', 'direction'
            ]),
            'users' => $users,
            'pipelineStages' => $pipelineStages,
            'pipelineTypes' => Opportunity::PIPELINE_TYPES,
            'statuses' => Opportunity::STATUSES,
            'priorities' => Opportunity::PRIORITIES,
            'stats' => $stats,
        ]);
    }

    /**
     * Display the kanban board view for opportunities.
     */
    public function kanban(Request $request): Response
    {
        $user = Auth::user();
        $company = $user->company;

        $pipeline = $request->get('pipeline', 'sales');
        $userId = $request->get('user_id');

        // Get kanban data
        $kanbanData = Opportunity::getKanbanData($pipeline, $userId);

        // Get users for filtering
        $users = User::where('company_id', $company->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get pipeline stats
        $stats = Opportunity::getPipelineStats($company->id, $pipeline, $userId);

        return Inertia::render('Opportunities/Kanban', [
            'kanbanData' => $kanbanData,
            'pipelineTypes' => Opportunity::PIPELINE_TYPES,
            'pipeline' => $pipeline,
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['pipeline', 'user_id']),
        ]);
    }

    /**
     * Show the form for creating a new opportunity.
     */
    public function create(Request $request): Response
    {
        $user = Auth::user();
        $company = $user->company;

        // Pre-fill data from lead if provided
        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::where('company_id', $company->id)
                ->findOrFail($request->get('lead_id'));
        }

        $users = User::where('company_id', $company->id)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $leads = Lead::where('company_id', $company->id)
            ->where('status', '!=', 'converted')
            ->select('id', 'first_name', 'last_name', 'company', 'email', 'phone')
            ->orderBy('company')
            ->get();

        return Inertia::render('Opportunities/Create', [
            'users' => $users,
            'leads' => $leads,
            'lead' => $lead,
            'pipelineTypes' => Opportunity::PIPELINE_TYPES,
            'pipelineStages' => Opportunity::DEFAULT_PIPELINE_STAGES,
            'statuses' => Opportunity::STATUSES,
            'priorities' => Opportunity::PRIORITIES,
            'types' => Opportunity::TYPES,
            'sources' => Opportunity::SOURCES,
            'preFillData' => $request->only(['lead_id']),
        ]);
    }

    /**
     * Store a newly created opportunity in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'account_name' => 'nullable|string|max:255',
            'pipeline' => 'required|string|in:' . implode(',', array_keys(Opportunity::PIPELINE_TYPES)),
            'stage' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'priority' => 'required|string|in:' . implode(',', array_keys(Opportunity::PRIORITIES)),
            'type' => 'nullable|string|in:' . implode(',', array_keys(Opportunity::TYPES)),
            'source' => 'nullable|string|in:' . implode(',', array_keys(Opportunity::SOURCES)),
            'user_id' => 'nullable|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'decision_maker' => 'nullable|string|max:255',
            'decision_makers' => 'nullable|array',
            'competitors' => 'nullable|string|max:255',
            'competitive_advantages' => 'nullable|string',
            'competitive_weaknesses' => 'nullable|string',
            'expected_close_date' => 'nullable|date|after_or_equal:today',
            'expected_close_days' => 'nullable|integer|min:1',
            'next_steps' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
            'team_members' => 'nullable|array',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $company = Auth::user()->company;

        // Set company and creator
        $validated['company_id'] = $company->id;
        $validated['created_by'] = Auth::id();

        // Set default values
        $validated['status'] = 'active';
        $validated['stage_order'] = Opportunity::getStageOrder($validated['stage']);

        // Calculate initial probability
        $opportunity = new Opportunity($validated);
        $opportunity->probability = $opportunity->calculateProbability();

        DB::beginTransaction();
        try {
            $opportunity->save();

            // Update lead status if linked
            if ($opportunity->lead_id) {
                $lead = Lead::find($opportunity->lead_id);
                if ($lead) {
                    $lead->status = 'opportunity_created';
                    $lead->save();
                }
            }

            // Record creation activity
            Activity::create([
                'company_id' => $company->id,
                'user_id' => Auth::id(),
                'subject_type' => Opportunity::class,
                'subject_id' => $opportunity->id,
                'action' => 'created',
                'description' => "Created opportunity: {$opportunity->title}",
                'properties' => [
                    'title' => $opportunity->title,
                    'amount' => $opportunity->amount,
                    'stage' => $opportunity->stage,
                    'pipeline' => $opportunity->pipeline,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Opportunity created successfully',
                'opportunity' => $opportunity->load(['user:id,name', 'lead:id,first_name,last_name,company'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create opportunity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified opportunity.
     */
    public function show(Opportunity $opportunity): Response
    {
        $this->authorize('view', $opportunity);

        $opportunity->load([
            'user:id,name,email',
            'creator:id,name,email',
            'winner:id,name,email',
            'loser:id,name,email',
            'lead:id,first_name,last_name,company,email,phone,status',
            'activities' => function ($query) {
                $query->with('user:id,name,email')
                      ->orderBy('created_at', 'desc')
                      ->limit(50);
            }
        ]);

        // Get similar opportunities
        $similarOpportunities = Opportunity::where('company_id', $opportunity->company_id)
            ->where('id', '!=', $opportunity->id)
            ->where(function ($query) use ($opportunity) {
                $query->where('account_name', $opportunity->account_name)
                      ->orWhere('contact_email', $opportunity->contact_email);
            })
            ->with('user:id,name')
            ->limit(5)
            ->get();

        return Inertia::render('Opportunities/Show', [
            'opportunity' => $opportunity,
            'similarOpportunities' => $similarOpportunities,
            'pipelineStages' => Opportunity::getPipelineStages($opportunity->pipeline),
            'statuses' => Opportunity::STATUSES,
            'priorities' => Opportunity::PRIORITIES,
        ]);
    }

    /**
     * Show the form for editing the specified opportunity.
     */
    public function edit(Opportunity $opportunity): Response
    {
        $this->authorize('update', $opportunity);

        $opportunity->load(['lead:id,first_name,last_name,company']);

        $users = User::where('company_id', $opportunity->company_id)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $leads = Lead::where('company_id', $opportunity->company_id)
            ->where('status', '!=', 'converted')
            ->select('id', 'first_name', 'last_name', 'company', 'email', 'phone')
            ->orderBy('company')
            ->get();

        return Inertia::render('Opportunities/Edit', [
            'opportunity' => $opportunity,
            'users' => $users,
            'leads' => $leads,
            'pipelineTypes' => Opportunity::PIPELINE_TYPES,
            'pipelineStages' => Opportunity::getPipelineStages($opportunity->pipeline),
            'statuses' => Opportunity::STATUSES,
            'priorities' => Opportunity::PRIORITIES,
            'types' => Opportunity::TYPES,
            'sources' => Opportunity::SOURCES,
        ]);
    }

    /**
     * Update the specified opportunity in storage.
     */
    public function update(Request $request, Opportunity $opportunity): JsonResponse
    {
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'account_name' => 'nullable|string|max:255',
            'pipeline' => 'required|string|in:' . implode(',', array_keys(Opportunity::PIPELINE_TYPES)),
            'stage' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'priority' => 'required|string|in:' . implode(',', array_keys(Opportunity::PRIORITIES)),
            'type' => 'nullable|string|in:' . implode(',', array_keys(Opportunity::TYPES)),
            'source' => 'nullable|string|in:' . implode(',', array_keys(Opportunity::SOURCES)),
            'user_id' => 'nullable|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'decision_maker' => 'nullable|string|max:255',
            'decision_makers' => 'nullable|array',
            'competitors' => 'nullable|string|max:255',
            'competitive_advantages' => 'nullable|string',
            'competitive_weaknesses' => 'nullable|string',
            'expected_close_date' => 'nullable|date',
            'expected_close_days' => 'nullable|integer|min:1',
            'next_steps' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            'team_members' => 'nullable|array',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Update stage order if stage changed
        if ($validated['stage'] !== $opportunity->stage) {
            $validated['stage_order'] = Opportunity::getStageOrder($validated['stage']);
        }

        $opportunity->update($validated);

        // Recalculate probability
        $opportunity->updateProbability();

        return response()->json([
            'success' => true,
            'message' => 'Opportunity updated successfully',
            'opportunity' => $opportunity->load(['user:id,name', 'lead:id,first_name,last_name,company'])
        ]);
    }

    /**
     * Remove the specified opportunity from storage.
     */
    public function destroy(Opportunity $opportunity): JsonResponse
    {
        $this->authorize('delete', $opportunity);

        DB::beginTransaction();
        try {
            $title = $opportunity->title;
            $opportunity->delete();

            // Record deletion activity
            Activity::create([
                'company_id' => $opportunity->company_id,
                'user_id' => Auth::id(),
                'subject_type' => Opportunity::class,
                'subject_id' => $opportunity->id,
                'action' => 'deleted',
                'description' => "Deleted opportunity: {$title}",
                'properties' => [
                    'title' => $title,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Opportunity deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete opportunity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move opportunity to a different stage.
     */
    public function moveStage(Request $request, Opportunity $opportunity): JsonResponse
    {
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'new_stage' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $opportunity->moveToStage($validated['new_stage'], $validated['user_id']);

            return response()->json([
                'success' => true,
                'message' => 'Opportunity moved successfully',
                'opportunity' => $opportunity->load(['user:id,name'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move opportunity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark opportunity as won.
     */
    public function markAsWon(Request $request, Opportunity $opportunity): JsonResponse
    {
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'actual_amount' => 'nullable|numeric|min:0',
            'won_reason' => 'nullable|string|max:1000',
        ]);

        try {
            $opportunity->markAsWon(
                $validated['actual_amount'] ?? null,
                $validated['won_reason'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Opportunity marked as won successfully',
                'opportunity' => $opportunity->load(['winner:id,name'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark opportunity as won: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark opportunity as lost.
     */
    public function markAsLost(Request $request, Opportunity $opportunity): JsonResponse
    {
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'lost_reason' => 'required|string|max:1000',
        ]);

        try {
            $opportunity->markAsLost($validated['lost_reason']);

            return response()->json([
                'success' => true,
                'message' => 'Opportunity marked as lost successfully',
                'opportunity' => $opportunity->load(['loser:id,name'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark opportunity as lost: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update opportunity probability.
     */
    public function updateProbability(Opportunity $opportunity): JsonResponse
    {
        $this->authorize('update', $opportunity);

        try {
            $opportunity->updateProbability();

            return response()->json([
                'success' => true,
                'message' => 'Probability updated successfully',
                'probability' => $opportunity->probability,
                'weighted_amount' => $opportunity->weighted_amount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update probability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update opportunities.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opportunity_ids' => 'required|array',
            'opportunity_ids.*' => 'exists:opportunities,id',
            'updates' => 'required|array',
            'updates.user_id' => 'nullable|exists:users,id',
            'updates.priority' => 'nullable|string|in:' . implode(',', array_keys(Opportunity::PRIORITIES)),
            'updates.stage' => 'nullable|string',
            'updates.status' => 'nullable|string|in:' . implode(',', array_keys(Opportunity::STATUSES)),
        ]);

        $company = Auth::user()->company;

        // Ensure all opportunities belong to the user's company
        $opportunities = Opportunity::where('company_id', $company->id)
            ->whereIn('id', $validated['opportunity_ids'])
            ->get();

        DB::beginTransaction();
        try {
            $updatedCount = 0;

            foreach ($opportunities as $opportunity) {
                $this->authorize('update', $opportunity);

                $opportunity->update($validated['updates']);

                if (isset($validated['updates']['stage'])) {
                    $opportunity->stage_order = Opportunity::getStageOrder($validated['updates']['stage']);
                    $opportunity->save();
                }

                $updatedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Updated {$updatedCount} opportunities successfully"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update opportunities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pipeline statistics for dashboard.
     */
    public function getPipelineStats(Request $request): JsonResponse
    {
        $company = Auth::user()->company;
        $pipeline = $request->get('pipeline', 'sales');
        $userId = $request->get('user_id');

        $stats = Opportunity::getPipelineStats($company->id, $pipeline, $userId);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}