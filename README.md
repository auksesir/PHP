# PHP

Prototype web site. I concentrated on functionality not the CSS of this project. I used MySQL database to store user details and modules related data. Modules related data, which contains moduleCode, studentID and modulesResults columns, is uploaded to the database from the files found in the data folder. I manipulate that data to create tables displayed on the module results view of the website. The database is accessed securely using prepared statements. On the ‘user Administration’ view, which is accessible only by admin type users (there are admin and academic type of users), I implemented CRUD functionality. 


Process of design

To keep PHP and HTML separate I have used separate html files that are included in the main html form of the page when needed.

‘Home Page’, ‘Module Results’ and ‘user Administration’ views can be accessed via menu navigation bar. ‘user Administration’ view with CRUD functionality is accessable only to the admin type user. ‘New user’, ‘Edit user’ forms, ‘View user’ table and ‘Delete user’ link can be accessed via icons in the table or above the table on the ‘User Administration’ view. Each row of the table has as full name of one of the existing users in the database. User deletion is confirmed via a pop up. There are ‘Go back’ buttons at the bottom of the page if the user is editing, viewing a user, or creating a new user. There are headings above ‘New user’ and ‘Edit user’ forms. The latter contains the full name of a user that is being edited. ‘View user’ table has a title which contains the full name of a user that is being viewed. 

Tables on the module results table are displayed horizontally, there is no need to scroll down the page to view the tables. Titles of the tables are displayed above the tables. 

Log in form changes to a full name of the logged in user and a log out button after a successful logging in. 


Implementation

Firstly, I started by making the log in and navigation between views work properly. I made sure that ‘academic’ users could not access the ‘User Administration’ view. Then I built the ‘Module Results’ view and created a table with links on the ‘User Administration’ view. 

After that I created a ‘New user’ form, a ‘View user’ table and an ‘Edit user’ form which are displayed after the appropriate icon is selected on the ‘User Aministration’ view. 

Lastly, I created a ‘Delete user’ pop up. 

I included appropriate messages that are displayed to the user when they interact with the website.

For the repetitive code reusable functions were created, for example, ‘formOutput($formPlaceholders, $file)’, ‘validateFormData($formData, $pdo).’ All my created functions are stored in ‘functions.php’. 

To connect to the database ‘config.php’ was created. I used PDO to access the database. I, also, made sure that the database is accessed securely by using prepared statements. 

To create a log-in I used a log in form with placeholders which are validated every time the submit button of the log in is pressed. 

![7](https://user-images.githubusercontent.com/85522584/212575256-fe069691-ca66-47e0-96b9-647f0b8216dd.jpg)


User Admin view (academic type user does not have access to)

To create icon links on the user administration view, I passed additional values to the URL. To open ‘View user’ table, ‘New user’ and ‘Edit user’ forms, I have passed ‘viewUser’, ‘newUser’, ‘editUser’ as property values to an ‘action’ variable. To know which user was selected to view or edit I have used an ‘id’ variable to pass id values of the user selected in the URL. As an ‘action’ parameter as well as ‘view’ parameter are passed via URL it is first checked if it exists in the ‘views’ folder of the project to avoid errors being thrown. This is done in ‘index.php’ on line 82 and in ‘userAdmin.php’ on line 12. I have also created a ‘Go back’ button that takes the user back to the initial ‘User Administration’ view, if the user is viewing, editing, or creating a new user. The button link contains a placeholder, to make sure that it takes the user back to the ‘Home Page’, in cases when a logged in ‘admin’ user is changed to an ‘academic’ type user. Also, in such cases, if the user tried selecting a ‘User Administration’ navigation option after becoming a type ‘academic’ user, the ‘Home Page’ is displayed instead, and the ‘User Administration’ navigation option disappears.



![8](https://user-images.githubusercontent.com/85522584/212575262-1f6bfa7d-72ca-4873-8457-f74d686560eb.jpg)


New User

![5](https://user-images.githubusercontent.com/85522584/212575200-0ec23e08-4037-47e1-ae15-47178a184e70.jpg)

![6](https://user-images.githubusercontent.com/85522584/212575205-dfcd2ed4-e658-4731-8787-d3826b2b9bb2.jpg)

To create a new user, I have used a form with placeholders. The user data needs to be validated before being stored in the database. Success or error messages are displayed after the submit button is pressed.

View User

![3](https://user-images.githubusercontent.com/85522584/212575165-7da6f12a-d75a-4a56-ac19-a506d1f8dae7.jpg)

A table containing all the selected user’s information is displayed. I made sure that the table does not overflow depending on the length of the data displayed.

Edit User

![4](https://user-images.githubusercontent.com/85522584/212575193-0dfccc4e-2102-4782-bb0f-9861a0b49087.jpg)

Placeholders are used to fill in the form with the existing information. When the submit button is pressed the data in the form is validated. Then it is checked if the ‘userName’ was not changed, if unchanged the ‘userName’ error is unset. It means that if the rest of the inputted information comply to the requirements, in the database existing user information is updated. If username of the logged in user is changed, the session variable ‘$_SESSION['uName']’ is updated. Furthermore, when the user that is being edited is a logged in user, the type of the user is also checked to see if it has been changed from ‘admin’ to ‘academic’.

Delete User

![2](https://user-images.githubusercontent.com/85522584/212575160-fb127d5e-61fd-441d-beb9-f6d077fbbf6c.jpg)

To delete a user, I have added ‘#popUp’ to the URL. So that after selecting one of the delete icons a pop up appears asking if the user should be deleted. The delete icon is a submit button which returns the id value of a selected user. When a user confirms the deletion, the selected user is deleted from the database. I have made sure that the initial admin user and the logged in users cannot be deleted. Every time after one of the users is deleted, on the ‘User Administration’ view displayed table is automatically updated.

Module results

![1](https://user-images.githubusercontent.com/85522584/212575154-1acc9a05-9a1d-4494-aad7-f70335fa9143.jpg)

There is a function that uploads new data from the files to the database, if it is not uploaded yet. The tables are displayed horizontally on the page. To create a flex layout, class attribute is given to the containing the tables.

Testing

I was testing the code while I was writing it. I, also, have made sure to test the website well after implementing all the code. I have checked ‘Log in’ form inputs, ‘New user’ form inputs and ‘Edit user’ form inputs. I have checked what errors are displayed to the user, if inputs that do not meet the validation requirements, are put in. I have checked what happens if a logged in user of a type ‘admin’ is changed to a user type ‘academic’ by using ‘edit user’ form. I made sure that if in such cases the user selects ‘Go back’ button or the ‘User Administration’ option on the navigation menu, the user is directed back to the ‘Home Page’. I, also, have checked what happens if the username of the logged in user is changed. Furthermore, I have checked if ‘delete user’ link is working properly. I, also, have tested the ‘Log out’ button. Lastly, I have checked what happens if incorrect parameters are passed to URL manually or database is not connected and what error messages are displayed to the user in such cases.

Improvements

When it comes to testing the code, I will think more in detail about use cases before starting to write the code. Especially, exceptional cases such as logged in ‘admin’ type user losing its access to the ‘User Administration’ view when being changed to ‘academic’ type user. Also, such cases like the logged in user’s name being changed, as it affects other parts of the website.
