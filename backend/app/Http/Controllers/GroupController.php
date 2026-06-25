<?php

namespace App\Http\Controllers;

use App\Http\Requests\group\AddGroupMemberRequest;
use App\Http\Requests\group\StoreGroupRequest;
use App\Http\Requests\group\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Services\GroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    // inject group service to handle logic
    public function __construct(private GroupService $groupService) {}

    // list all groups the user belongs to
    public function index(): JsonResponse
    {
        $groups = $this->groupService->getUserGroups(Auth::id());

        return response()->json([
            'success' => true,
            'data' => GroupResource::collection($groups),
        ]);
    }

    // create a new group and assign owner
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

    // get details of a specific group
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

    // update group details
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

    // permanently delete a group
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

    // add a new member via phone number
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

    // remove a member from the group
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

    // allow the user to exit a group
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
