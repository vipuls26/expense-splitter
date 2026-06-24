<?php

namespace App\Http\Controllers;

use App\Http\Requests\group\AddGroupMemberRequest;
use App\Http\Requests\group\StoreGroupRequest;
use App\Http\Requests\group\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Services\GroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    // Inject the group service to handle all group business logic
    public function __construct(private GroupService $groupService) {}

    // Return all groups the logged-in user is part of
    public function index(): JsonResponse
    {
        $groups = $this->groupService->getUserGroups(Auth::id());
        
        return response()->json([
            'success' => true,
            'data' => GroupResource::collection($groups),
        ]);
    }

    // Create a new group with the logged-in user as the owner
    public function store(StoreGroupRequest $request): JsonResponse
    {
        $group = $this->groupService->createGroup(
            $request->validated(),
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Group created successfully',
            'data' => new GroupResource($group),
        ], 201);
    }

    // Get details of a single group by its ID
    public function show(int $id): JsonResponse
    {
        $group = $this->groupService->getGroupById(
            $id,
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'data' => new GroupResource($group),
        ]);
    }

    // Update the name or details of a group
    public function update(
        UpdateGroupRequest $request,
        int $id
    ): JsonResponse {
        $group = $this->groupService->updateGroup(
            $id,
            $request->validated(),
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Group updated successfully',
            'data' => new GroupResource($group),
        ]);
    }

    // Permanently delete a group — only the owner can do this
    public function destroy(int $id): JsonResponse
    {
        $this->groupService->deleteGroup(
            $id,
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Group deleted successfully',
        ]);
    }

    // Add a new member to a group using their phone number
    public function addMember(
        AddGroupMemberRequest $request,
        int $id
    ): JsonResponse {
        $group = $this->groupService->addMemberToGroup(
            $id,
            $request->validated()['phone_no'],
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Member added successfully',
            'data' => new GroupResource($group),
        ]);
    }

    // Remove a specific member from the group
    public function removeMember(
        int $id,
        int $memberId
    ): JsonResponse {
        $group = $this->groupService->removeMemberFromGroup(
            $id,
            $memberId,
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully',
            'data' => new GroupResource($group),
        ]);
    }

    // Let the logged-in user exit a group they are part of
    public function leave(int $id): JsonResponse
    {
        $this->groupService->leaveGroup(
            $id,
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Successfully left the group',
        ]);
    }
}
