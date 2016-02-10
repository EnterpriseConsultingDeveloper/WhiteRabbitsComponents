# S3FileManager plugin for CakePHP

## Installation

You can install the plugin cackephp-utils into your CakePHP application using [composer](http://getcomposer.org) and executing the
following lines in the root of your application.

```
composer require whiterabbit/s3filemanager 
 ```
 
 ## Configuration
 
 You will need to add the following line to your application's bootstrap.php file:
 
 ```php
 Plugin::load('S3FileManager');
 ```
 
 And in your AppController add
 
 ```php
 use S3FileManager\Controller\WRTrait;
 
 class AppController extends Controller
 {
     use WRTrait;
 }
 
```    
    
In order to pass a customer name / site to the UploadableBehavior you have to put it in the parameter session Auth.User.customer_site (ie in AppController, beforeFilter method):

```php
 public function beforeFilter(Event $event)
 {
   $this->request->session()->write('Auth.User.customer_site', 'my.customer.site');

 } 
```

For the S3File Helper, in your AppController add

```php
public function initialize() {
    $this->helpers[] = 'S3FileManager.S3File';
}
```

To access Amazon resources, this plugin need the Aws SDK installed. You can download the v3 versione of the SDK from here
 http://docs.aws.amazon.com/aws-sdk-php/v3/guide/getting-started/installation.html and follow instruction under the section 
 "Installing via Zip".
    

## version

0.2.1
- S3FileHelper: Set the default image instead of an html code
- Added a customer site name to the uploadable behavior in order to create, in S3, separate bucket for different customers.  
if you want use this feature add the parameter Auth.User.customer_site in your session


0.1.3.1
Solved sum bug on S3FileHelper

0.1.3
Added S3FileHelper helper

0.1.2
Added folder management for S3

0.1.1
Initial release

License
-------

The MIT License (MIT)

Copyright (c) 2016 WhiteRabbit by Dino Fratelli

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.