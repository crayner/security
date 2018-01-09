Hill Range Security Bundle
==========================
This bundle is specifically written for use with the Symfony 4 Security Bundle.


User Tracking
-------------
This interface is implemented by the trait UserTrackTrait. To implement this interface
add a use statement in the calling class to use the trait.  The listeners will then
identify your entity, add the necessary fields and insert appropriate data as the
entity is used.  It is actually done with smoke and mirrors.

The User Fields Listener alteres the schema of your class adding the necessary fields
to track both creation and modification of entity rows.  The User Track Listener does
the work of adding the field data for newly created and modified entity rows.  Doctrine
is smart enough to ensure that these changes only occur if other fields are actually
changed on modification.  On creation both the modified and create fields are filled
with data.  Once the created fields have valid data they are ignored.  The modified
fields are altered on each change of data to the table row.


Idle Timeout
------------
Idle timeout is available does not work unless your app uses jquery.  The bundle will
detect that jquery is active and load all of the appropriate scripts to engage
idletimeout.