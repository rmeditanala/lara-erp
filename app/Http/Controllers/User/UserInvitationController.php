<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserInvitationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('permission:invite users');
    }

    /**
     * Display a listing of invitations.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            abort(403, 'You must belong to a company to view invitations.');
        }

        $invitations = $company->userInvitations()
            ->with('inviter')
            ->latest()
            ->paginate(10);

        return Inertia::render('Users/Invitations/Index', [
            'invitations' => $invitations,
        ]);
    }

    /**
     * Show the form for creating a new invitation.
     */
    public function create(): Response
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            abort(403, 'You must belong to a company to invite users.');
        }

        // Get available roles (excluding super-admin)
        $availableRoles = [
            'company-owner' => 'Company Owner',
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'sales-rep' => 'Sales Representative',
            'employee' => 'Employee',
            'read-only' => 'Read Only',
        ];

        // Only super-admins can invite other super-admins
        if (!$user->hasRole('super-admin')) {
            unset($availableRoles['company-owner']);
        }

        return Inertia::render('Users/Invitations/Create', [
            'availableRoles' => $availableRoles,
        ]);
    }

    /**
     * Store a newly created invitation in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            abort(403, 'You must belong to a company to invite users.');
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) use ($company) {
                    return $query->where('company_id', $company->id);
                }),
                Rule::unique('user_invitations')->where(function ($query) use ($company) {
                    return $query->where('company_id', $company->id)
                                 ->whereNull('accepted_at')
                                 ->where('expires_at', '>', now());
                }),
            ],
            'role' => [
                'required',
                'string',
                Rule::in([
                    'company-owner',
                    'admin',
                    'manager',
                    'sales-rep',
                    'employee',
                    'read-only'
                ])
            ],
        ]);

        // Only super-admins can invite company owners
        if (!$user->hasRole('super-admin') && $validated['role'] === 'company-owner') {
            abort(403, 'You do not have permission to invite company owners.');
        }

        DB::transaction(function () use ($validated, $company, $user) {
            $invitation = UserInvitation::createInvitation([
                'company_id' => $company->id,
                'invited_by' => $user->id,
                'email' => $validated['email'],
                'role' => $validated['role'],
            ]);

            // Send invitation email (for now, we'll just log it)
            // In a real application, you would send an email here
            \Log::info("Invitation sent to {$validated['email']} for company {$company->name} with role {$validated['role']}");
        });

        return redirect()->route('users.invitations.index')
            ->with('success', 'Invitation sent successfully!');
    }

    /**
     * Display the invitation acceptance form.
     */
    public function show($token)
    {
        $invitation = UserInvitation::where('token', $token)
            ->with(['company', 'inviter'])
            ->firstOrFail();

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired.');
        }

        return Inertia::render('Auth/InvitationAccept', [
            'invitation' => $invitation,
        ]);
    }

    /**
     * Accept the invitation and create user account.
     */
    public function accept(Request $request, $token)
    {
        $invitation = UserInvitation::where('token', $token)
            ->firstOrFail();

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated, $invitation) {
            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
                'company_id' => $invitation->company_id,
                'phone' => $validated['phone'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'email_verified_at' => now(), // Auto-verify email since invitation was sent to this address
                'is_active' => true,
            ]);

            // Assign the role
            $user->assignRole($invitation->role);

            // Mark invitation as accepted
            $invitation->accept();
        });

        return redirect()->route('login')
            ->with('success', 'Account created successfully! You can now log in.');
    }

    /**
     * Remove the specified invitation from storage.
     */
    public function destroy(UserInvitation $invitation)
    {
        $user = Auth::user();

        if ($invitation->company_id !== $user->company_id) {
            abort(403, 'You can only delete invitations for your own company.');
        }

        if ($invitation->isAccepted()) {
            abort(403, 'Cannot delete an accepted invitation.');
        }

        $invitation->delete();

        return redirect()->route('users.invitations.index')
            ->with('success', 'Invitation deleted successfully.');
    }
}
