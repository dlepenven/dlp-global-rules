# itop-global-rules

## Description
This is an extension for the product iTop by Combodo : https://www.itophub.io/
It allows an admin to create rules on any object.
The rules can do the following:
- Test all configuration
- Edit a value (of any type, but no linkedSet)
- Apply a stimuli
- Add elements to direct or indirect linkedSet
- Add a tab on objects that have had a trigger

## Warnings
The effects of rules could be heavy. Please test this module before installing it on production environments.

## Install
This extension has been tested for iTop 2.5.0 and newer.
Use the editor documentation to install extensions : https://www.itophub.io/wiki/page

## Configuration
It is better to load this extension when all others are loaded. So You will have to add necessary dependencies into `module.dlp-global-rules.php` depending on your itop instance.

## Create your first rule
* From the "Admin Tools" menu, click on the link : "Rules on objects configuration" and start creating a rule
* Fill the form
    * Fill the name and desctiption fields as desired
    * Choose the status "enable" if you want to enable your rule. If not, the rule will never be triggered.
    * Choose the trigger type : Create will trigger the rules on object creation only, update for object update only. 
    * Fill a valid target class (Ex: UserRequest)
    * Fill a valid OQL condition on the current object (Ex: (service_id=3 OR service_id=4) AND title='test')
    * Fill values to apply this way
        * One value per line
        * To set a value : "value:field_id:text", ex: "value:title:This is my new title" will change the title to "This is my new title". The quotes are not needed
        * To apply a stimuli : "stimuli:stimuli_name", ex: "stimuli:ev_assign" will trigger assign stimuli.
        * To set a new entry in a linkedSet : "link:field_id:col1/val1:col2/val2" etc... Ex : "link:contact_list:contact_id/3:role_code/do_not_notify" will add the contact with id 3 with the role 'Do not notify' to the object
        * All values are applied in given order.
        
![Create](readme/imgs/create.png?raw=true "Create")

## Tests
A test tab is available on every rules. It tests the target class, the condition on it and the values. It does not test the logic in the values.
If a test is not valid, the rule will not be applied
![Test OK](readme/imgs/test.png?raw=true "Test OK") 

![Test NOK](readme/imgs/test_nok.png?raw=true "Test NOK")

## How does it works
Today it only works when creating a new object. It is not correct to enable if for updates since it could create many loops.
The values are applied considering the rights of the current user. For example, if a user does not have the right to apply a stilumi, it can trigger an error.

## Limits
For the moment, following characters are reserved to parse the configuration : 
- `: /`

## Ideas
- Better parsing / Config area
- Check mandatory fields
- Try to catch errors
- etc...

## Third part
- Icon : https://www.iconfinder.com/Juliia_Os
- CSS : Bootstrap 4