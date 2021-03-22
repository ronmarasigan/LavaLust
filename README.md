## LavaLust Version 2
	This is an early release of LavaLust Version 2. You may check the changelog.txt file to see the changes.
###	Overview of Changes in Version 2
#### Note: you can still use the framework version 2 the way you use the version 1 before
	-> $this->load is now $this->call
	-> super object get_instance is now lava_instance()
	->folder structures but you can still change it to your preferred names and path
		* application is now app
		* system is now scheme
		* assets is now public
		* core is now kernel
		* benchmark class is now performance. see the changelog.txt for more details
		* Constants and mimes file inside config folder was also deleted. We are making the framework as simple and as
		light as possible
	->cache is now inside runtime folder
### Update Version 1 to Version 2
	1. Backup your files..
	2. Replace all files and directories in your system/ directory with the files and directories from scheme/ folder of version 2.
	3. If you are using the constants and mimes that were deleted in version 2, please take note of those things and update your app manually. 
## What is LavaLust?
	LavaLust is an lightweight Web Framework - (using MVC pattern) - for people who are developing web sites using PHP. It helps
	you write code easily using Object-Oriented Approach. It also provides set of libraries for commonly needed tasks, as well as
	a helper functions to minimize the amount of time coding.

	LavaLust is somehow similar to CodeIgniter 3 structure and call to commonly used functions but way different when it comes to
	class construction. This is very simple and ultimately light. The main purpose of this is to teach the basics of OOP and how
	it will work with MVC. You will see in the documentation the similarities I am pertaining to.

## Installation and Tutorials

[Github Page](https://ronmarasigan.github.io)

[Youtube Channel](https://youtube.com/ronmarasigan) 

## Special Thanks/Credits to the following
	CodeIgniter (some helpers, libraries and many other things)
	HTMLPurifier (for XSS filtering)
	Github Comunity / Youtube for all the resouces I read to make this work.

### Licence
	MIT License

	Copyright (c) 2020 Ronald M. Marasigan

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