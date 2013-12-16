#AS3-obfuscator

Simple obfuscator for ActionScript 3 classes written in PHP5 and based on regular expressions.

It replaces the names of classes and class methods to random strings founded in all AS3 files to confuse the logic of reading scripts.


##How does this work

Put the structure of classes in proc/source directory and run index.php.

In case of successful obfuscation you will obtain information in an array of three-dimensional structure "package/class/method" and the replacement table, plus the finished files to proc/destination.


##Things to Consider

To prevent obfuscate of the class name (and constructor), for example, a based start-class (usually, Main.as) initialized from the *.fla you must add it to the file proc/ignored-words.txt.

If somewhere in your files you call constructor through concatenating strings you will obtain the value of class name/method only at compilation process or in the execution of program. In this case this obfuscator it makes no sense to use. If you really want it, you need to rewrite your code.
