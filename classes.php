<?php


class User{
    public $Userid;
    public $Username;
    public $PasswordHash;
    public $Email;
    public $Role;

}

class Student{
    public $userid;
    public $firstName ;
    public $lastName;
    public $nationality;
    public $registrationdate;
}

class Instructor{
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
