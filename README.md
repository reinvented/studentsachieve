# class.studentsachieve.php
## A PHP Class for Interacting with StudentsAchieve

This is the beginnings of a PHP class for interacting with the [StudentsAchieve student records management system](http://office.studentsachieve.com/corporate/) used in Prince Edward Island for storing information about student attendance and marks.

**The code here does nothing more than mimic the behaviour of a regular old web browser: to make use of it you need a valid "parent access" username and password for StudentsAchieve.**

*This code is an independent open source software project not affiliated with StudentsAchieve Software.*

The code is early in development and is limited in function. Right now you can use it simply to automate a login to StudentsAchieve and retrieve a list of your child's classes and teachers and their email addresses.

### Limitations

* Only tested for the Birchwood Intermediate School StudentsAchieve; other schools, even ones on PEI, might have slight differences that prevent this from working. Let me know your experiences.
* Only handles the situation where there is a single student attached to the parent's login; I don't have a way of testing a multi-student household, so I cannot adapt at present.

### Sample Code

The included script **get-teacher-vcard.php** will login, retrieve teachers and classes and email addresses and create a vCard file suitable for important into an "address book" or "contacts" application. To run this test from the command line:

	php get-teacher-vcard.php username password
	
Where "username" and "password" are the parent access username and password assigned to you by your child's school administration. 

The result will be a file named **[schoolname].vcf** that you can then load into any address book application that will import vCards.
