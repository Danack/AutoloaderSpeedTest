AutoloaderSpeedTest
===================

A test project for various speed optimizations for Composers auto class loading.

Why?
====

I'm an ex-video games developer. I can't stand seeing huge chunks of memory being used when it isn't necessary.

After running `composer--optimize-autoloader update` the 'optimised' version of the class loader looks like this: http://pastebin.com/Xa1ii7PY which quite frankly is appalling. It takes up 645kB of memory, which is about 30% of the memory needed to display a page in the framework I use.

Some sort of optimization for figuring out where the files for classes are is needed as file_exists() calls are too darn slow, and must be avoided at all costs.

This repository holds a couple of caching attempts, including the on that you're probably interested in:


OPCachingClassLoader
====================

OPCache stores optimized versions of PHP files. We can use that as a cache of which files have already been loaded in a previous PHP script, which effectively gives us a cache of what files contain which classes (from a small list of available possibilities). 

The OPCachingClassLoader requires you to use my fork of OPCache from https://github.com/Danack/ZendOptimizerPlus as it exposes the function opcache_script_cached($scriptName).
