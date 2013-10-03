Egn
===

A PHP class for validation and generation of EGN (personal identification numbers for Bulgarian citizens)

Examples:

```php
// EGN generation, for testing purposes.
$egn = Egn::generate();
echo $egn;
// Sample result: 9509125507
// The result is a random valid EGN, not necessarily a real person posseses it.

echo '<br />';

$egn = '9306295605';
$is_valid = Egn::valid($egn);
// $is_valid should be TRUE as a result.
echo $egn.' - '.($is_valid ? 'Valid' : 'Invalid');

echo '<br />';

$egn = '9306295606';
$is_valid = Egn::valid($egn);   // Boolean result
// $is_valid should be FALSE as a result.
echo $egn.' - '.($is_valid ? 'Valid' : 'Invalid');

echo '<br />';

$egn = '9306295605';
$burthday = Egn::get_birthday($egn);
// $burthday should be 1993-06-29
echo $egn.' - Birthday is '.$burthday;

echo '<br />';

$egn = '9306295605';
$gender = Egn::get_gender($egn);
// $gender should be 'm' as a result ('m' for male, 'f' for female).
echo $egn.' - Gender is '.$gender;

echo '<br />';
```

Links:

http://georgi.unixsol.org/programs/egn.php

http://georgi.unixsol.org/diary/archive.php/2006-09-29
