<?php
// includeing nessacry classes/files
require_once("classes.php");
require_once("business.php");
// getting the requests uri and method from _$SERVER
$url = $_SERVER["REQUEST_URI"];
$method = $_SERVER["REQUEST_METHOD"];
//$url for making it as an array instead of checking it as /login /logout etc .. we can use $url[0]-> the uri
$url = explode("/",$url);
// Starting the sessions to indentify the users at each request in the web app
session_start();
// routing the user to a root page
if($url[1]==""){
    showSinglePage();
// if the request uri is /login it will check the Method
// if its a POST then it will do the following
}else if($url[1]=="login"){
    if($method == "POST"){
        // getting the input form from the html login page
        $username = filter_input(INPUT_POST,"username");
        $password = filter_input(INPUT_POST,"password");
        // checking the login credientials

        $res = Checklogin($username,$password);
        //  its obvious lol :D
        if(!$res==1){
            $errorMessage = "Incorrect username or password.";
            showLogin($errorMessage);
            die(0);
        }
        // This method to prevent session fixation to make sure each session has  unique identifier
        session_regenerate_id(true);
        // getting the users to from the database storing in sessions > i wont explain everything here 
        $uesrObjectResult = fetchSingleUserInfo($username);
        $_SESSION['username']= $uesrObjectResult->Username;
        $_SESSION['Role']= $uesrObjectResult->Role;
        $_SESSION['Userid']= $uesrObjectResult->Userid;
        if($_SESSION['Role']==0){
            print_r("Working");
            header("Location:/admin");
            die(0);
        }elseif($_SESSION['Role']==2){
            print_r("Student!!!");
            die(0);
        }

    }elseif($method=="GET"){
        session_destroy();
        showLogin(null);
        die(0);

    }

// its obvious what this route do.. handling /admin route
}elseif($url[1]=="admin"){
    if(!isset($_SESSION["username"])){
        session_destroy();
        header("Location:/login");
        
    }else{
        if($_SESSION["Role"]!=0
        ){
            session_destroy();
            header("Location:/login");
        }else{
            showAdminPage();
            die(0);
        }
    }
    // Session Kill !!! 
}elseif($url[1]=="logout"){
    session_destroy();
    header("Location:/login");
    // Below method to add student 
   // Add_student only authorized admins can do it 
}elseif($url[1]=="add_course"){
        // Check if the user is logged in and has the appropriate role
        if (!isset($_SESSION["username"]) || $_SESSION["Role"] != 0) {
            // Redirect unauthorized users to the admin panel
            header("Location: /admin");
            die();
        }
        $successMessage = ""; // to confirm that the courses added scucees fully

        if($method=="POST"){

            $courseCode = filter_input(INPUT_POST,"courseCode",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $courseName =  filter_input(INPUT_POST,"courseName",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $courseCredits =filter_input(INPUT_POST,"courseCredits",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $CourseObject = new Course;
            $CourseObject->code=$courseCode;
            $CourseObject->name=$courseName;
            $CourseObject->credits=$courseCredits;
            checkCourseBeforeCreate($CourseObject);
            die(0);

        }elseif($method == "GET"){
            http_response_code(403); // Method Not Allowed
            notAllowed();
            die();
        }
}elseif($url[1]=="add_user"){
    if (!isset($_SESSION["username"]) || $_SESSION["Role"] != 0) {
        // Redirect unauthorized users to the admin panel
        header("Location: /admin");
        die();
    }if($method =="POST"){
        $username = filter_input(INPUT_POST,"username",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST,"password",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST,"email",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $role = filter_input(INPUT_POST,"role",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $userObject = new User;
        $userObject->Username=$username;
        $userObject->PasswordHash=$password;
        $userObject->Email=$email;
        $userObject->Role=$role;
        checkUserBeforeCreate($userObject);
        die(0);


    }
    else{
        http_response_code(403); // GET  Method Not Allowed
        notAllowed();
        die(0);
    }

}
elseif($url[1]=="add_instructor"){
    if (!isset($_SESSION["username"]) || $_SESSION["Role"] != 0) {
        // Redirect unauthorized users to the admin panel
        header("Location: /admin");
        die();
    }
    if($method=="POST"){

        $username = filter_input(INPUT_POST,"username",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST,"password",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST,"email",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $role = filter_input(INPUT_POST,"role",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $first_name = filter_input(INPUT_POST,"ifirstName",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $last_name = filter_input(INPUT_POST,"ilastName",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $dapartment = filter_input(INPUT_POST,"department",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $instructorObject = new Instructor;
        $userObject = new User;
        $userObject->Username=$username;
        $userObject->PasswordHash=$password;
        $userObject->Email=$email;
        $userObject->Role=$role;
        $instructorObject->firstName=$first_name;
        $instructorObject->lastName=$last_name;
        $instructorObject->department=$dapartment;
        checkInstructorBeforeCreate($instructorObject,$userObject);
        die(0);
    }else{
        http_response_code(403); // GET  Method Not Allowed
        notAllowed();
        die(0);
    }
}elseif ($url[1] == "add_student") {
    // Check if the user is logged in and has the appropriate role
    if (!isset($_SESSION["username"]) || $_SESSION["Role"] != 0) {
        // Redirect unauthorized users to the admin panel
        header("Location: /admin");
        die(0);
    }




    $successMessage = ""; // << check this out later

    // Process the "add_student" request
    if ($method == "POST") {
        $username = filter_input(INPUT_POST,"username",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST,"password",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST,"email",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $role = filter_input(INPUT_POST,"role",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $first_name = filter_input(INPUT_POST,"firstName",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $last_name = filter_input(INPUT_POST,"lastName",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $nationality = filter_input(INPUT_POST,"nationality",FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $studentObject = new Student;
        $userObject = new User;
        $userObject->Username=$username;
        $userObject->PasswordHash=$password;
        $userObject->Email=$email;
        $userObject->Role=$role;
        $studentObject->firstName=$first_name;
        $studentObject->lastName=$last_name;
        $studentObject->nationality=$nationality;
        $studentObject->registrationdate = date("Y-m-d");
        checkStudentBeforeCreate($studentObject,$userObject);// to check for any illegal entries and then it will create 
        die(0);
        // Get method are not allowd for this route even for admin , thats why i need to handle this 
    } elseif ($method == "GET") {
        // Handle GET requests
        http_response_code(403); // Method Not Allowed
        notAllowed();
        die();
    }

    // getting all the users from the database its only for admins as it shown
}elseif ($url[1] == "AllUsers") {

     if(!isset($_SESSION["username"])){
         session_destroy();
         header("Location:/login");
         die(0);
        
     }else{
         if($_SESSION["Role"]!=0){
            // i could modify this so the normal user does not have to relogin again << ill check this later 
             session_destroy();
             print("Not allowed For reguler Users");
             die(0);
         }else{
             fetchAllUsersInfo();
             die(0);
         }
     }
}elseif($url[1]=="AllCourses"){
    if(!isset($_SESSION["username"])){
        session_destroy();
        header("Location:/login");
        die(0);
       
    }else{
        if($_SESSION["Role"]!=0){
            // i could modify this so the normal user does not have to relogin again << ill check this later 
            session_destroy();
            print("Not allowed For reguler Users");
            die(0);
        }else{
            fecthAllCoursesInfo();
            die(0);
        }
    }




}else{
    notFound();
}
