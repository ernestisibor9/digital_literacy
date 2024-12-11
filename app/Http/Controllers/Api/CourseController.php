<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseStoreRequest;
use App\Http\Requests\CourseUpdateRequest;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $courses = $this->courseService->getAllCourses();
        return response()->json(['courses' => $courses], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CourseStoreRequest $request)
    {
        //
        $course = $this->courseService->createCourse($request->validated());

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $course = $this->courseService->getCourseById($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return response()->json(['course' => $course], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CourseUpdateRequest $request, $id)
    {
        //
        $course = $this->courseService->updateCourse($id, $request->validated());

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $deleted = $this->courseService->deleteCourse($id);

        if (!$deleted) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return response()->json(['message' => 'Course deleted successfully'], 200);
    }
}
