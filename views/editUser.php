<?php

# getting ID of the user that was selected to edit
$_SESSION['id'] = $_GET['id'];
$id = $_SESSION['id'];

$content = '';
$link = 'userAdmin'; 
# getting data of a selected user from the database
$data []= SQLformattedViewUsers($id, $pdo); 
$data = $data[0];

if ($data != 'error') {
    
    # finding full name of the user whose data is being edited
    $name = userFullName($data['userName'], $pdo); 
    $userFullName = checkFullName($name); # checking if no error was reported
    $fullName = $userFullName[0];
    $content .= $userFullName[1];
    # creating heading
    $content .= htmlHeading('Viewing details for selected user: ' . $fullName, 4);
    $initialUserType = $data['userType']; # used to check if signed in admin lost its access

    if (!isset($_POST['Submitted'])) {
        
        $id = $_SESSION['id'];
        $formPlaceholders1 = findPlaceholdersValues($data);
        # displaying user's data
        $output = formOutput($formPlaceholders1, 'html/userForm.html');
        $content .= $output;
    } else { # edited data submitted  
            $formData = validateFormData($_POST, $pdo); 
            #extract clean data, errors and placeholders and placeholder data from arrays returned
            $cleanData = $formData[0];
            $errors = $formData[1];
            $formPlaceholders2 = $formData[2];
                
            if($data['userName'] == $formPlaceholders2['[+uName+]']) { # as only unique user names are allowed to be written into databse, unsetting user name
                unset($errors['uName']);                               # error message, if the user name was not changed, makes sure that the edited information
                $formPlaceholders2['[+uNameError+]'] = '';             # gets updated in the database even if the user name was not changed
            } else if ($data['userName'] == $_SESSION['uName']) { # checking if logged in user's username has changed
                $_SESSION['uName'] = $formPlaceholders2['[+uName+]'];
            }
                    
            if(empty($errors)) {
                # updating users data
                $update = updateUsersTable($_POST, $id, $pdo);
                if ($update == "noError") { # appropriate message is printed out
                    $newName = $cleanData['fName'] . ' ' . $cleanData['sName'];
                    $content .=  htmlParagraph("Details of the user $newName were updated.", False);
                } else {
                    $content .= htmlParagraph("ERROR details of the user were not updated.", False);
                }
            } else {
                #print out form with error messages
                $output = formOutput($formPlaceholders2, 'html/userForm.html');
                $content .= $output;
            }
            # checking if logged in admin type was changed to academic
            if (checkLostAccess($data['userName'], $formPlaceholders2, $initialUserType)) {     
                $link = 'home'; # if yes go back button should take user to home page
            }
        }
    } else {
        $content .= htmlParagraph("ERROR user was not found.", False); 
    }
# adding go back button to the bottom of the page  

$button = addButton($link);
$content .= $button;
   
 ?>