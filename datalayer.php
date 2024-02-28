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

function fetchSingleUser($username){
    $db = dabs();

    $userObject = new User;
    $getUser = $db->prepare("select * from users where Username=:user");
    $getUser ->bindParam("user",$username);
    $getUser ->execute();
    $resUser = $getUser ->fetchAll(PDO::FETCH_OBJ);
    foreach($resUser as $k => $i){
    // $x->room_id = $i->room_id;
    // $x->building = $i->building;
    // $x->name = $i->name;
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