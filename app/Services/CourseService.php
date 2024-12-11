<?php
    namespace App\Services;

    use App\Models\Course;

    class CourseService
    {
        public function getAllCourses()
        {
            return Course::with('instructor')->get();
        }

        public function getCourseById($id)
        {
            return Course::with('instructor')->find($id);
        }

        public function createCourse(array $data)
        {
            return Course::create($data);
        }

        public function updateCourse($id, array $data)
        {
            $course = Course::find($id);

            if (!$course) {
                return null;
            }

            $course->update($data);

            return $course;
        }

        public function deleteCourse($id)
        {
            $course = Course::find($id);

            if (!$course) {
                return false;
            }

            $course->delete();

            return true;
        }
    }
