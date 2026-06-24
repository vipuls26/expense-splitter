<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\GroupServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class GroupController extends Controller
{
    public function __construct(private GroupServiceInterface $groupService)
    {}

    public function index()
    {
        try {
            $userId = Auth::id();
            $groups = $this->groupService->getUserGroups($userId);

            return response()->json([
                'success' => true,
                'data' => $groups
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve groups',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $group = $this->groupService->createGroup($request->all(), $userId);

            return response()->json([
                'success' => true,
                'message' => 'Group created successfully',
                'data' => $group
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $userId = Auth::id();
            $group = $this->groupService->getGroupById($id, $userId);

            return response()->json([
                'success' => true,
                'data' => $group
            ]);
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
        
            $statusCode = in_array($statusCode, [403, 404]) ? $statusCode : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $group = $this->groupService->updateGroup($id, $request->all(), $userId);

            return response()->json([
                'success' => true,
                'message' => 'Group updated successfully',
                'data' => $group
            ]);
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            $statusCode = in_array($statusCode, [403, 404]) ? $statusCode : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function destroy($id)
    {
        try {
            $userId = Auth::id();
            $this->groupService->deleteGroup($id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Group deleted successfully'
            ]);
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            $statusCode = in_array($statusCode, [403, 404]) ? $statusCode : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function addMember(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'phone_no' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $group = $this->groupService->addMemberToGroup($id, $request->phone_no, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Member added successfully',
                'data' => $group
            ]);
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            $statusCode = in_array($statusCode, [400, 403, 404]) ? $statusCode : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function removeMember($id, $memberId)
    {
        try {
            $userId = Auth::id();
            $group = $this->groupService->removeMemberFromGroup($id, $memberId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully',
                'data' => $group
            ]);
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            $statusCode = in_array($statusCode, [400, 403, 404]) ? $statusCode : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function leave($id)
    {
        try {
            $userId = Auth::id();
            $this->groupService->leaveGroup($id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Successfully left the group'
            ]);
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            $statusCode = in_array($statusCode, [400, 403, 404]) ? $statusCode : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
