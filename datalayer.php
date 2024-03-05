<?php
require_once("classes.php");

function dabs(){
    $db = new PDO('mysql:host=localhost;dbname=education','root','');
    $db ->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    return $db;
}
function Checklogin($username , $password){
    $db = dabs();
    $x = new User;
    $hashedPassword = hash('sha256', $password);
    $getCredintails = $db ->prepare(" select count(*) from Users where Username=:user and PasswordHash=:pass ;");
    $getCredintails->bindParam("user",$username);
    $getCredintails->bindParam(":pass",$hashedPassword);
    $getCredintails->execute();
    $res = $getCredintails->fetch(PDO::FETCH_COLUMN);
    return $res;

}
function getFees(Payment $payment){
    $db=dabs();
    $arrayOfResults=[];
    $getFee=$db->prepare("SELECT Courses.name , Courses.credits FROM Enrollments
    JOIN Courses ON Enrollments.CourseID = Courses.CourseID
    WHERE Enrollments.StudentID = :studentID");
    $getFee->bindParam("studentID",$payment->studentID);
    $getFee->execute();
    $getFeeResult = $getFee->fetchAll(PDO::FETCH_OBJ);
    foreach($getFeeResult as $k =>$i){
        $newObject = new $payment;
        $newObject->name=$i->name;
        $newObject->credits=$i->credits;
        array_push($arrayOfResults,$newObject);
    }
    return $arrayOfResults;


}
function checkStudentEnrolled($studentID,$courseID){
    $db = dabs();

    $checkStudent = $db->prepare("SELECT COUNT(*) FROM Enrollments WHERE StudentID = :sid AND CourseID = :cid;");
    $checkStudent->bindParam(":sid",$studentID);
    $checkStudent->bindParam(":cid",$courseID);
    $checkStudent->execute();
    $res = $checkStudent->fetch(PDO::FETCH_COLUMN);
    return $res;





}


function assignNewGrade($enrollment,$grade){
    $db=dabs();
    $newGrade= $db->prepare("INSERT INTO grades(EnrollmentID , GradeValue) VALUES (:eid,:value); ");
    $newGrade->bindParam(":eid",$enrollment);
    $newGrade->bindParam(":value",$grade);
    $newGrade->execute();
    return true;
}

function checkBeforeAssignNewGrade($enrollmentID){
    $flag = false;
    $db=dabs();
    $newGrade = $db->prepare("SELECT COUNT(*) From grades WHERE EnrollmentID = :eid");
    $newGrade->bindParam(":eid",$enrollmentID);
    $newGrade->execute();
    $count=$newGrade->fetchColumn();
    return $count>0; // if true then it should not proccedd and renter the grade again ;; :) 


}
// to avoid instructor tamper the data
function validateInstructorBeforeSubmissionGrade($enrollmentID, $instructorID){
    $db=dabs();
    $checkEnrollment = $db->prepare("SELECT COUNT(*) FROM Enrollments WHERE EnrollmentID = :eid AND InstructorID = :iid;");
    $checkEnrollment->bindParam(":eid", $enrollmentID);
    $checkEnrollment->bindParam(":iid", $instructorID);
    $checkEnrollment->execute();
    $count = $checkEnrollment->fetchColumn(); // Fetch the count directly
    return $count > 0; // Return true if count is greater than 0, indicating the enrollment is for the instructor


}
//// testing
function getStudentsByInstructor($instructorID){
    $studentsByCourse = [];

    try{
        $db = dabs();
        // Fetch unique course IDs taught by the instructor
        $courseIDsQuery = $db->prepare("SELECT DISTINCT CourseID FROM Enrollments WHERE InstructorID = :instructorID");
        $courseIDsQuery->bindParam(":instructorID", $instructorID);
        $courseIDsQuery->execute();
        $courseIDsResult = $courseIDsQuery->fetchAll(PDO::FETCH_COLUMN);

        foreach($courseIDsResult as $courseID){
            // Fetch course details
            $courseQuery = $db->prepare("SELECT * FROM courses WHERE CourseID = :courseID");
            $courseQuery->bindParam(":courseID", $courseID);
            $courseQuery->execute();
            $courseResult = $courseQuery->fetch(PDO::FETCH_OBJ);

            if($courseResult) {
                // Fetch students enrolled in the course taught by the given instructor
                $studentsQuery = $db->prepare("SELECT * FROM Enrollments WHERE CourseID = :courseID AND InstructorID = :instructorID");
                $studentsQuery->bindParam(":courseID", $courseID);
                $studentsQuery->bindParam(":instructorID", $instructorID);
                $studentsQuery->execute();
                $studentsResult = $studentsQuery->fetchAll(PDO::FETCH_OBJ);

                foreach($studentsResult as $student){
                    // Fetch student details
                    $studentQuery = $db->prepare("SELECT * FROM Students WHERE StudentID = :studentID");
                    $studentQuery->bindParam(":studentID", $student->StudentID);
                    $studentQuery->execute();
                    $studentResult = $studentQuery->fetch(PDO::FETCH_OBJ);

                    if($studentResult) {
                        // Add student details to the array indexed by course name
                        $courseName = $courseResult->name;
                        $studentInfo = [
                            'enrollmentID' => $student->EnrollmentID,
                            'firstName' => $studentResult->FirstName, // Update column name
                            'lastName' => $studentResult->LastName // Update column name
                        ];
                        if (!isset($studentsByCourse[$courseName])) {
                            $studentsByCourse[$courseName] = [];
                        }
                        $studentsByCourse[$courseName][] = $studentInfo;
                    }
                }
            }
        }
    } catch(PDOException $e){
        echo "Error: " . $e->getMessage();
    }

    return $studentsByCourse;
}

function getStudentGrades($studentID){
    $db = dabs();
    $arrayOfClasses = [];
    $uniqueCourseIDs = [];

// Querying classes from the enrollment table
    $classes = $db->prepare("SELECT EnrollmentID, CourseID FROM Enrollments WHERE StudentID = :id;");
    $classes->bindParam(":id", $studentID);
    $classes->execute();
    $classesResult = $classes->fetchAll(PDO::FETCH_OBJ);

// Fetching course names and grades from the enrollment object to get the IDs
    foreach ($classesResult as $class) {
    // Fetch course name
        $courseQuery = $db->prepare("SELECT name FROM Courses WHERE CourseID = :courseID;");
        $courseQuery->bindParam(":courseID", $class->CourseID);
        $courseQuery->execute();
        $courseResult = $courseQuery->fetch(PDO::FETCH_OBJ);

        // Fetch grade
        $gradeQuery = $db->prepare("SELECT GradeValue FROM Grades WHERE EnrollmentID = :enrollmentID;");
        $gradeQuery->bindParam(":enrollmentID", $class->EnrollmentID);
        $gradeQuery->execute();
        $gradeResult = $gradeQuery->fetch(PDO::FETCH_OBJ);

        // Creating StudentGrade object
        $studentGrade = new StudentGrade();
        $studentGrade->CourseName = $courseResult->name;
        $studentGrade->Grade = $gradeResult ? $gradeResult->GradeValue : "";

        // Adding StudentGrade object to array
        array_push($arrayOfClasses, $studentGrade);

        }

        return $arrayOfClasses;



}

function getClassesByStudent($studentID){
    $arrayOfClasses = [];
    $uniqueCourseIDs = []; // Track unique course IDs

    
    try{
        $db = dabs();
        $classes = $db->prepare("SELECT * FROM Enrollments WHERE StudentID = :id;");
        $classes->bindParam(":id", $studentID);
        $classes->execute();
        $classesResult = $classes->fetchAll(PDO::FETCH_OBJ);
        
        foreach($classesResult as $k => $i){
            // Check if course ID already exists in uniqueCourseIDs array
            if(!in_array($i->CourseID, $uniqueCourseIDs)){
                $InstructorCourse = new InstructorCourse;

                $courseQuery = $db->prepare("SELECT * FROM courses WHERE CourseID = :courseID");
                $courseQuery->bindParam(":courseID", $i->CourseID);
                $courseQuery->execute();
                $courseResult = $courseQuery->fetch(PDO::FETCH_OBJ);
                $InstructorCourse->courseName=$courseResult->name;
                $InstructorCourse->enrollmentDate=$i->EnrollmentDate;
                $uniqueCourseIDs[] = $i->CourseID;
                array_push($arrayOfClasses,$InstructorCourse);
            }
        }

    } catch(PDOException $e){
        echo "Error: " . $e->getMessage();
    }

    return $arrayOfClasses;
}





function getClassesByInstructor($instructorID){
    $arrayOfClasses = [];
    $uniqueCourseIDs = []; // Track unique course IDs

    
    try{
        $db = dabs();
        $classes = $db->prepare("SELECT * FROM Enrollments WHERE InstructorID = :id;");
        $classes->bindParam(":id", $instructorID);
        $classes->execute();
        $classesResult = $classes->fetchAll(PDO::FETCH_OBJ);
        
        foreach($classesResult as $k => $i){
            // Check if course ID already exists in uniqueCourseIDs array
            if(!in_array($i->CourseID, $uniqueCourseIDs)){
                $InstructorCourse = new InstructorCourse;

                $courseQuery = $db->prepare("SELECT * FROM courses WHERE CourseID = :courseID");
                $courseQuery->bindParam(":courseID", $i->CourseID);
                $courseQuery->execute();
                $courseResult = $courseQuery->fetch(PDO::FETCH_OBJ);
                $InstructorCourse->courseName=$courseResult->name;
                $InstructorCourse->enrollmentDate=$i->EnrollmentDate;
                $uniqueCourseIDs[] = $i->CourseID;
                array_push($arrayOfClasses,$InstructorCourse);
            }
        }

    } catch(PDOException $e){
        echo "Error: " . $e->getMessage();
    }

    return $arrayOfClasses;
}


function createNewEnrollment($enrollment){
    $flag = false;
    try{
        $db=dabs();
        $addEnrollment = $db->prepare("INSERT INTO Enrollments (CourseID, StudentID, InstructorID, EnrollmentDate) VALUES (:cid, :sid, :insid, :edate)");
        $addEnrollment->bindParam(":cid",$enrollment->courseID);
        $addEnrollment->bindParam(":sid",$enrollment->studentID);
        $addEnrollment->bindParam(":insid",$enrollment->instructorID);
        $addEnrollment->bindParam(":edate",$enrollment->enrollmentDate);
        $addEnrollment->execute();
        $flag = true;
    }catch(PDOException $e){
        echo "Error:".$e->getMessage();
    }
    return $flag;
}

// for enroll GET method
function getAllInstructorInfo(){

    $db=dabs();
    $instructorInfo=$db->prepare("SELECT * from instructors;");
    $instructorInfo->execute();
    $res = $instructorInfo->fetchAll(PDO::FETCH_OBJ);
    $arrayOfInstructors=[];
    foreach($res as $k =>$i){

        $instructorObject = new Instructor;
        $instructorObject->instructorID=$i->InstructorID;
        $instructorObject->userid=$i->UserID;
        $instructorObject->firstName=$i->FirstName;
        $instructorObject->lastName=$i->LastName;
        $instructorObject->department=$i->Department;
        array_push($arrayOfInstructors,$instructorObject);

    }

    return $arrayOfInstructors;

}


function getAllStudentInfo(){
    $db=dabs();
    $studentInfo=$db->prepare("SELECT * from students;");
    $studentInfo->execute();
    $res = $studentInfo->fetchAll(PDO::FETCH_OBJ);
    $arrayOfStudents=[];
    foreach($res as $k =>$i){

        $studentObject = new Student;
        $studentObject->studentID=$i->StudentID;
        $studentObject->userid=$i->UserID;
        $studentObject->firstName=$i->FirstName;
        $studentObject->lastName=$i->LastName;
        array_push($arrayOfStudents,$studentObject);

    }


    return $arrayOfStudents;

}



function fetchSingleInstructor($id){
    $db = dabs();

    $instructorObject = new Instructor;
    $getInstructor = $db->prepare("select * from instructors where UserID=:userid");
    $getInstructor->bindParam("userid",$id);
    $getInstructor->execute();
    $resInstructor =  $getInstructor ->fetchAll(PDO::FETCH_OBJ);
    foreach($resInstructor as $k => $i){
    $instructorObject->instructorID=$i->InstructorID;
    $instructorObject->userid=$i->UserID;
    $instructorObject->firstName=$i->FirstName;
    $instructorObject->lastName=$i->LastName;
    $instructorObject->department=$i->Department;
    }

    return $instructorObject;


}

function fetchSingleUser($username){
    $db = dabs();

    $userObject = new User;
    $getUser = $db->prepare("select * from users where Username=:user");
    $getUser ->bindParam("user",$username);
    $getUser ->execute();
    $resUser = $getUser ->fetchAll(PDO::FETCH_OBJ);
    foreach($resUser as $k => $i){
    $userObject->Userid=$i->UserID;
    $userObject->Username=$i->Username;
    $userObject->Role=$i->Role;
    }

    return $userObject;


}

function fetchAllUsers(){
    $db = dabs();

    $getAllUsers=$db->prepare("select * from Users");
    $getAllUsers->execute();
    $resultAllUsers =$getAllUsers->fetchAll(PDO::FETCH_OBJ);
    $arrayOfUsers=[];
    foreach($resultAllUsers as $k =>$i){
        $userObject = New User;
        $userObject->Userid=$i->UserID;
        $userObject->Username=$i->Username;
        $userObject->Email=$i->Email;
        $userObject->Role=$i->Role;
        array_push($arrayOfUsers,$userObject);
    }

    return $arrayOfUsers;

}


function fetchAllCourses(){

    $db = dabs();

    $getAllCourses=$db->prepare("select * from courses");
    $getAllCourses->execute();
    $resultAllCourses =$getAllCourses->fetchAll(PDO::FETCH_OBJ);
    $arrayOfCourses=[];
    foreach($resultAllCourses as $k =>$i){
        $courseObject = new Course;
        $courseObject->CourseID = $i->CourseID;
        $courseObject->code = $i->code;
        $courseObject->name = $i->name;
        $courseObject->credits = $i->credits;
        array_push($arrayOfCourses,$courseObject);
    }

    return $arrayOfCourses;



}

function createNewCourse(Course $course){
    $flag = false;
    try{
        //first check if the course exist on the databases 
        $db=dabs();
        $checkCourse = $db->prepare("SELECT COUNT(*) FROM courses WHERE code = :code ");
        $checkCourse->bindParam(":code",$course->code);
        $checkCourse->execute();
        $checkCourseCount=$checkCourse->fetchColumn();
        if($checkCourseCount>0){
            return $flag;
        }else{
            $addCourse = $db->prepare("INSERT INTO courses (code,name,credits) VALUES (:code, :name, :credits)");
            $addCourse->bindParam(":code",$course->code);
            $addCourse->bindParam(":name",$course->name);
            $addCourse->bindParam(":credits",$course->credits);
            $addCourse->execute();
            $flag=true;

        }
    }catch(PDOException $e){
        echo "Error:".$e->getMessage();
    }

    return $flag;
}
// a flag to check if its sucess or not 
function createNewStudent(Student $student , User $user): bool{
    $flag = false;
    try{
        $db = dabs();
        $result = checkUserExist($user);
        if ($result) { 
            return $flag;
            //return $flag;
        }
        // // else continue ; 
        $addUser = $db->prepare("INSERT INTO users (Username, PasswordHash, Email, Role) VALUES (:username, :password, :email, :role)");
        $addUser->bindParam(':username', $user->Username);
        $addUser->bindParam(':password', $user->PasswordHash);
        $addUser->bindParam(':email', $user->Email);
        $addUser->bindParam(':role', $user->Role);
        $addUser->execute();
        $userid = $db->lastInsertId();
        $addStudent = $db->prepare("INSERT INTO Students (UserID, FirstName, LastName, Nationality, RegistrationDate) VALUES (:userid, :firstName, :lastName, :nationality, :registrationDate)");
        $addStudent->bindParam(':userid', $userid);
        $addStudent->bindParam(':firstName', $student->firstName);
        $addStudent->bindParam(':lastName', $student->lastName);
        $addStudent->bindParam(':nationality', $student->nationality);
        $addStudent->bindParam(':registrationDate', $student->registrationdate);
        $addStudent->execute();
        $flag=true;
    
    }catch(PDOException $e){
        echo "Error: " . $e->getMessage();

    }

    return $flag;



}

function createNewUser(User $user){
    $flag = false;
    try{
        $db = dabs();
        $result = checkUserExist($user);
        if ($result) { 
            return $flag;
            //return $flag;
        }
        // // else continue ; 
        $addUser = $db->prepare("INSERT INTO users (Username, PasswordHash, Email, Role) VALUES (:username, :password, :email, :role)");
        $addUser->bindParam(':username', $user->Username);
        $addUser->bindParam(':password', $user->PasswordHash);
        $addUser->bindParam(':email', $user->Email);
        $addUser->bindParam(':role', $user->Role);
        $addUser->execute();
        $flag=true;
    
    }catch(PDOException $e){
        echo "Error: " . $e->getMessage();

    }

    return $flag;

}


// <<to check the user existance before creatain a student , admin or instructor >>
function checkUserExist(User $user){
    $db = dabs();
    $getUser = $db->prepare("select count(*) from users where Username=:user OR Email = :email");
    $getUser ->bindParam("user",$user->Username);
    $getUser ->bindParam("email",$user->Email);
    $getUser ->execute();
    $usernameCount = $getUser->fetchColumn();
    if($usernameCount>0){
        return true;
    }else{
        return false;
    }

    


}

function fetchSingleStudent($id){
    $db= dabs();
    $student = New Student;
    $getStudent = $db->prepare("SELECT * from Students where UserID  = :id;");
    $getStudent->bindParam(":id",$id);
    $getStudent->execute();
    $studentResult = $getStudent->fetch(PDO::FETCH_OBJ);
    $student->studentID=$studentResult->StudentID;
    $student->userid=$studentResult->UserID;
    $student->firstName=$studentResult->FirstName;
    $student->lastName=$studentResult->LastName;
    return $student;


}

function createNewInstructor(Instructor $instructor , User $user){
    $flag = false;
    try{
        $db = dabs();
        $result = checkUserExist($user);
        if ($result) { 
            return $flag;
            //return $flag;
        }
        // else continue ; 
        $addUser = $db->prepare("INSERT INTO users (Username, PasswordHash, Email, Role) VALUES (:username, :password, :email, :role)");
        $addUser->bindParam(':username', $user->Username);
        $addUser->bindParam(':password', $user->PasswordHash);
        $addUser->bindParam(':email', $user->Email);
        $addUser->bindParam(':role', $user->Role);
        $addUser->execute();
        $userid = $db->lastInsertId();
        $addStudent = $db->prepare("INSERT INTO instructors (UserID, FirstName, LastName, Department) VALUES (:userid, :firstName, :lastName, :department)");
        $addStudent->bindParam(':userid', $userid);
        $addStudent->bindParam(':firstName', $instructor->firstName);
        $addStudent->bindParam(':lastName', $instructor->lastName);
        $addStudent->bindParam(':department', $instructor->department);
        $addStudent->execute();
        $flag=true;
    
    }catch(PDOException $e){
        echo "Error: " . $e->getMessage();

    }

    return $flag;
}