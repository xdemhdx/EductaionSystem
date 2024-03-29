<?php

require_once("datalayer.php");
require_once("classes.php");
require_once("control.php");

//checking login information from login requst 
function loginCheck($username , $password){
    $res = Checklogin($username , $password);
    return $res;
}


function assignNewGradeToStudent($studentEnrollmentID,$grade){
    $instructorID = $_SESSION["instructorID"];
    $grade = intval($grade);
    $studentEnrollmentID=intval($studentEnrollmentID);

    if (empty($studentEnrollmentID) || empty($grade) ||
    !is_int($studentEnrollmentID) || !is_int($grade) ||
    $studentEnrollmentID < 0 || $grade < 0 || $grade > 100) 
    {
        invalidEntries();  
        die(0);
    }
    $result = validateEnrollmentForInstructor($studentEnrollmentID,$instructorID);
    if(!$result){
        print_r("<h1>unauthorized</h1>");
        die(0);
    }else{
        $res = checkBeforeAssignNewGrade($studentEnrollmentID);
        if($res){
            print_r("<h1>Its there you cannot insert into it again!!!</h1> ");
            die(0);

        }else{
            $theResult = assignNewGrade($studentEnrollmentID,$grade);
            if($theResult){

                newGradeSuccess();
                die(0);

            }else{
                print_r("Not Success");
                die(0);
            }
        }

    }



}

function showFinancialPage(){
    $payment = new Payment;
    $payment->studentID=$_SESSION["studentID"];
    $res = getFees($payment);
    // print_r($res);
    // die(0);
    $location = "payment.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["payment"=>$res]));

}
function showStudentPage(){
    $result = fetchSingleStudent($_SESSION["Userid"]);
    $_SESSION["studentID"]=$result->studentID;
    $location = "student.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["student"=>$result]));

}

function validateEnrollmentForInstructor($enrollmentID, $instructorID){
    return validateInstructorBeforeSubmissionGrade($enrollmentID, $instructorID);
}


function showGradePage(){
    $result = getStudentsByInstructor($_SESSION["instructorID"]);
    $location = "grades.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["courses"=>$result]));
    // print_r($result);
    // die(0);

}
function notFound(){
    $location = "404.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());

}

function getInstructorClass($instructorID){

    $result =getClassesByInstructor($instructorID);
    $location = "MyClass.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["courses"=>$result]));


}
function getStudentClass($studentID){
    $result =getClassesByStudent($studentID);
    $location = "MyClass.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["courses"=>$result]));
    
}


function getStudentGrade(){
    $studentID = $_SESSION["studentID"];
    $result = getStudentGrades($studentID);
    // print_r($result);
    // die(0);
    $location = "studentgrade.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["courses"=>$result]));

}


// dont forget current Date function !! 


function checkEnrollmentBeforeCreate(Enrollment $enrollment){
    $courseID = intval($enrollment->courseID);
    $instructorID = intval($enrollment->instructorID);
    $studentID = intval($enrollment->studentID);
    if(
        empty($courseID) || ($courseID<1)||
        empty($instructorID)|| ($instructorID<1)||
        empty($studentID) ||  ($studentID<1)
    ){
        invalidEntries();
        die(0);

    }else{
        $res = checkStudentEnrolled($studentID,$courseID);
        if($res){
            AlreadyEnrolled();
            die(0);
        }else{
            $result = createNewEnrollment($enrollment);
            if($result){
                SuccessEnrollment();
                die(0);

            }else{
                NotSuccessEnrollment();
                die(0);
            }
        }
    }


}
function makeEnroll(){
    $instructors = getAllInstructorInfo();
    $courses = fetchAllCourses();
    $students =getAllStudentInfo();
    $location = "enrollment.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["courses"=>$courses,"instructors"=>$instructors,"students"=>$students]));

}



function notAllowed(){
    $location = "403.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());

}

// rendering main page for the website e.g root screen
function showSinglePage(){
    $location = "main.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}
// rendering a route request for login , and $error message to check if login creditnals is correct or not 
function showLogin($errorMessage){
    $location = "login.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(['errorMessage' => $errorMessage]));
}

// once login success for the admin users , it will render an admin dashboard page
function showAdminPage(){
    $location = "admin.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}
// function getInstructorInfo(){
//     $result = fetchSingleInstructor($_SESSION["Userid"]);
//     return $result;
// }

function showInstructorPage(){

    $instructor = fetchSingleInstructor($_SESSION["Userid"]);
    $_SESSION["instructorID"]=$instructor->instructorID;
    $location = "instructor.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["instructor"=>$instructor]));

}


    // this method is for testing purpose -> ignore it ill remove it later
function showDataPage(){
    $location = "data.html";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}
// to fethc One single user information and store it in session to save the state of the user between the pages
function fetchSingleUserInfo($username){
    $userObject = fetchSingleUser($username);
    return $userObject;
}
// an Admin requests all USers to get all the information of the users table in the database;
function fetchAllUsersInfo(){
    $result = fetchAllUsers();
    $location = "AllUsers.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["users"=>$result]));
}

function fecthAllCoursesInfo(){
    $result = fetchAllCourses();
    $location = "courses.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["courses"=>$result]));


}


// this to create a new student and user this method revices 2 Objects as it showing go to datalayer to get an insigt what is happening
function createCourse(Course $course){
    return createNewCourse($course);

}


// is_int dont forget convert into intval() !! 
function checkCourseBeforeCreate(Course $course){

    $validCredits = [1, 2, 3, 4, 9];
    $courseCodePattern = '/^[A-Za-z0-9]+$/';
    $courseNamePattern = '/^[A-Za-z\s]+$/'; // Allow spaces in course name

    if (
        empty($course->code) || !preg_match($courseCodePattern, $course->code) ||
        empty($course->name) || !preg_match($courseNamePattern, $course->name) ||
        empty($course->credits) || !in_array($course->credits, $validCredits)
    ) {
        invalidEntries();
    } else {
        $res = createCourse($course);
        if($res){
            scucessMessage();
        }else{
            notSuccesCourseCreation();
        }
        die(0);
    }
    die(0);


}
function createUser(User $user){
    return createNewUser($user);
}

function checkUserBeforeCreate(User $user){
    if(!checkUserEntries($user)){
        invalidEntries();
        die(0);

    }else{
        $user->PasswordHash=hash("sha256",$user->PasswordHash);
        $res = createUser($user);
        if($res){
            scucessMessage();
        }else{
            notSuccesUserCreation();
        }
    }
}
function createInstructor(Instructor $instructor, User $user){
    return createNewInstructor($instructor,$user);
}
function createStudent(Student $stundet , User $user){

    return createNewStudent($stundet,$user);
}
// i seprated this function because ill use it for all roles

// is_int dont forget convert into intval() !! 
function checkUserEntries(User $user){
    $passwordLength = strlen($user->PasswordHash); // Assuming PasswordHash is the hashed password
    $validRoles = [0, 1, 2];
    $emailPattern= '/^[a-zA-Z0-9-_]{4,20}@udst\.edu\.qa$/';
    $usernamePattern = '/^6\d{7}+$/'; //6XXXXXXX is only accepted as UserName
    if(empty($user->Username) || !preg_match($usernamePattern, $user->Username) ||
    empty($user->PasswordHash) || empty($user->Email) || !preg_match($emailPattern,$user->Email)||
    !in_array($user->Role, $validRoles) || $passwordLength <= 8){

        return false;
    }else{

        return true;
    }

}


// is_int dont forget convert into intval() !! 
function checkInstructorBeforeCreate(Instructor $instructor ,User $user ){
    $namePattern = '/^[A-Za-z]+$/';
    if (
        (empty($instructor->firstName) || !preg_match($namePattern, $instructor->firstName) ||
        empty($instructor->lastName) || !preg_match($namePattern, $instructor->lastName) ||
        empty($instructor->department) || !preg_match($namePattern, $instructor->department) ) || (!checkUserEntries($user))) {
        // Handle case where values do not meet whitelist criteria
        invalidEntries();
        die(0);
    }else{
        $user->PasswordHash=hash("sha256",$user->PasswordHash);

        $res= createInstructor($instructor,$user);
        if($res){
            scucessMessage();
        }else{
            notSuccesUserCreation();
        }
    }
}



// is_int dont forget convert into intval() !!  
function checkStudentBeforeCreate(Student $student , User $user){
    $namePattern = '/^[A-Za-z]+$/'; // Alphabetic characters only
    // check for empty and illegal values before procedding
    //checkUserEntries($user);
    if (
        (empty($student->firstName) || !preg_match($namePattern, $student->firstName) ||
        empty($student->lastName) || !preg_match($namePattern, $student->lastName) ||
        empty($student->nationality) || !preg_match($namePattern, $student->nationality) ) || (!checkUserEntries($user))) {
        // Handle case where values do not meet whitelist criteria
        invalidEntries();
        die(0);
    } else {
        $user->PasswordHash=hash("sha256",$user->PasswordHash);
        $res = createStudent($student,$user);
        if($res){
                scucessMessage();
        }else{  
                notSuccesUserCreation();
        }
    }


}
function notSuccesCourseCreation(){
    echo "<div style'color='red' ' id='successMessage'>Not Success ; Course might exist</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = '/admin';
            }, 3000);
          </script>";

}
function invalidEntries(){
    echo "<div style='color:red;' id='errorMessage'>Invalid input values</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = '/admin';
            }, 3000);
          </script>";

}

function scucessMessage(){
    echo "<div id='successMessage'><h1>Success</h1></div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = '/admin';
            }, 3000);
        </script>";

}

function notSuccesUserCreation(){

    echo "<div style'color='red' ' id='successMessage'>Not Success ; username or email might exist</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = '/admin';
            }, 3000);
          </script>";


}

function AlreadyEnrolled(){
    echo "<div style='color: red; text-align: center;' id='successMessage'><h1>Student Already Enrolled in this Course</h1></div>";


    echo "<script>
            setTimeout(function() {
                window.location.href = '/enroll';
            }, 3000);
          </script>";

}


function NotSuccessEnrollment(){
    echo "<div style='color:red; text-align:center;' id='errorMessage'><h1>Enrollment Not successfull</h1></div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = '/enroll';
            }, 3000);
          </script>";

}


function SuccessEnrollment(){
    echo "<div style='color:green; text-align:center;' id='errorMessage'><h1>Enrollment Successfull redirecting...</h1></div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = '/enroll';
            }, 3000);
          </script>";

}



function newGradeSuccess(){

    echo "<div style='color:green; text-align:center;' id='errorMessage'><h1>Grade Insertion Successfull redirecting...</h1></div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = '/grade';
            }, 3000);
          </script>";


}