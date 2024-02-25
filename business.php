<?php

require_once("datalayer.php");
require_once("classes.php");
require_once("control.php");

function loginCheck($username , $password){
    $res = Checklogin($username , $password);
    return $res;
}


function showSinglePage(){
    $location = "main.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}

function showLogin(){
    $location = "login.html";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}
function showAdminPage(){
    $location = "admin.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}

function fetchSingleUserInfo($username){
    $userObject = fetchSingleUser($username);
    return $userObject;
}

function createStundet(Student $stundet , User $user){
    $res = createNewStudent($stundet,$user);
    if($res){
        echo "<div id='successMessage'>Success</div>";
        echo "<script>
                setTimeout(function() {
                    window.location.href = '/admin';
                }, 3000);
              </script>";
    }else{
    echo "<div style'color='red' ' id='successMessage'>Not Success</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = '/admin';
            }, 3000);
          </script>";

    }
}