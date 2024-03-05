<?php


class User{
    public $Userid;
    public $Username;
    public $PasswordHash;
    public $Email;
    public $Role;

}

class Student{
    public $studentID;
    public $userid;
    public $firstName ;
    public $lastName;
    public $nationality;
    public $registrationdate;
}

class Instructor{
    public $instructorID;
    public $userid;
    public $firstName ;
    public $lastName;
    public $department;

}

class Course {
    public $CourseID;
    public $code;
    public $name;
    public $credits;
}
class Enrollment {
    public $enrollmentID;
    public $courseID;
    public $studentID;
    public $instructorID;
    public $enrollmentDate;

}


class InstructorCourse {
    public $courseName;
    public $enrollmentDate;
}


class StudentGrade {
    public $CourseName;
    public $Grade;

}

class Payment {
    public $name;
    public $studentID;
    public $credits;
    public $amount;


}