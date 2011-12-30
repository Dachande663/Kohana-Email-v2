Kohana Email v2
===============

Kohana Email provides a standardised email interface for sending emails in the Kohana 3.2+ framework. It also provides a Spam Assassin score based on the excellent SpamChecker provided by Postmark (available to non-customers as well). This module does not used switfmailer out of personal distaste.


Current Transports
------------------

* mail()
* Postmark (http://postmarkapp.com/)

Additional transports can be added by simply making a new file in /classes/email and extending Email_Transport.


Installation Instructions
-------------------------

1. Copy Kohana Email to your modules directory (e.g. /modules/email)
2. Add Kohana Email to your modules array in bootstrap.php
3. Create a config file at /application/config/email.php and enter your details if necessary.
4. You're ready to start sending emails!


Example Email
-------------
```php
$email = Email::compose('my_config_group')
    ->to('newmember@mysite.com')
    ->cc('another-person@mysite.com')
    ->cc('someone-else@mysite.com')
    ->from('hello@mysite.com')
    ->reply('reply@mysite.com')
    ->subject('Welcome to My Site')
    ->body('Plain text welcome message')
    ->body(View::factory('emails/welcome'), 'html');

try {
    $email->send();
} catch(Email_Exception) {
    echo $e->getMessage();
}
```


Methods
-------

* to('username@domain.com') *
* cc(array('Proper Name', 'username@domain.com')) *
* bcc('username@domain.com') *
* from('username@domain.com') *
* reply('username@domain.com') *
* subject('My Subject Line')
* body('My body'[, type = 'html'])
* header('Message-ID', '<my.message@id.com>') *
* send()
* spam_score([verbose = false])

\* Can be called multiple times

NB: email addresses can be in the form 'username@domain.com' OR array('Proper Name', 'username@domain.com') and will be expanded to be "Proper Name <username@domain.com>".


Thanks
------

* http://kohanaframework.org/
* http://postmarkapp.com