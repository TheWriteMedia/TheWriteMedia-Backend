<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all services
        $services = Service::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Services retrieved successfully',
            'services' => $services,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:marketing,publishing',
            'imageUrl' => 'nullable|url',
            'additional_info' => 'nullable|array', // Ensure additional_info is an array
            'additional_info.*' => 'string', // Each item in additional_info must be a string
            'inclusions_table' => 'nullable|array', // Ensure inclusions_table is an array
            'inclusions_table.*.columns' => 'required|array', // Each inclusion must have columns
            'inclusions_table.*.columns.*.name' => 'required|string', // Each column name is required
            'inclusions_table.*.columns.*.price' => 'nullable|string', // Column price is optional
            'inclusions_table.*.group_data' => 'required|array', // Each inclusion must have group data
            'inclusions_table.*.group_data.*.category' => 'required|string', // Each group must have a category
            'inclusions_table.*.group_data.*.rows' => 'required|array', // Each group must have rows
            'inclusions_table.*.group_data.*.rows.*.service' => 'required|string', // Each row must have a service
            'inclusions_table.*.group_data.*.rows.*' => 'required|array', // Each row must have values for all columns
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        $service = Service::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'imageUrl' => $request->imageUrl,
            'additional_info' => $request->type === 'marketing' ? $request->additional_info : null,
            'inclusions_table' => $request->type === 'publishing' ? $request->inclusions_table : null,
            'status' => 'ACTIVE',
        ]);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Service stored successfully',
            'service' => $service,
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        // Return the specified service
        return response()->json([
            'status' => 'success',
            'message' => 'Service retrieved successfully',
            'service' => $service,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
    
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255', // Only validate if present
            'description' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:marketing,publishing',
            'imageUrl' => 'nullable|url',
            'additional_info' => 'nullable|array', // Ensure additional_info is an array
            'additional_info.*' => 'string', // Each item in additional_info must be a string
            'inclusions_table' => 'nullable|array', // Ensure inclusions_table is an array
            'inclusions_table.*.columns' => 'required|array', // Each inclusion must have columns
            'inclusions_table.*.columns.*.name' => 'required|string', // Each column name is required
            'inclusions_table.*.columns.*.price' => 'nullable|string', // Column price is optional
            'inclusions_table.*.group_data' => 'required|array', // Each inclusion must have group data
            'inclusions_table.*.group_data.*.category' => 'required|string', // Each group must have a category
            'inclusions_table.*.group_data.*.rows' => 'required|array', // Each group must have rows
            'inclusions_table.*.group_data.*.rows.*.service' => 'required|string', // Each row must have a service
            'inclusions_table.*.group_data.*.rows.*' => 'required|array', // Each row must have values for all columns
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        // Find the service by ID
        $service = Service::find($id);
    
        if (!$service) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found',
            ], 404);
        }
    
        // Ensure the user owns the service
        if ($service->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this service',
            ], 403);
        }
    
        // Update the service fields
        $service->title = $request->has('title') ? $request->title : $service->title;
        $service->description = $request->has('description') ? $request->description : $service->description;
        $service->type = $request->has('type') ? $request->type : $service->type;
        $service->imageUrl = $request->has('imageUrl') ? $request->imageUrl : $service->imageUrl;
    
        // Update additional_info if type is marketing
        if ($request->has('additional_info') && $request->type === 'marketing') {
            $service->additional_info = $request->additional_info;
        } else {
            $service->additional_info = null; // Clear additional_info if type is not marketing
        }
    
        // Update inclusions_table if type is publishing
        if ($request->has('inclusions_table') && $request->type === 'publishing') {
            $service->inclusions_table = $request->inclusions_table;
        } else {
            $service->inclusions_table = null; // Clear inclusions_table if type is not publishing
        }
    
        // Save the updated service
        $service->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Service updated successfully',
            'service' => $service,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        // Delete the service
        $service->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Service deleted successfully',
        ], 200);
    }
}