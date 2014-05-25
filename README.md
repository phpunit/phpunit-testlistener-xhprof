phpunit-testlistener-xhprof
===========================

A TestListener for PHPUnit that uses XHProf for automated profiling of the tested code.

Setup and Configuration
-----------------------
Add the following to your `composer.json` file
```json
{
    "require-dev": {
        "phpunit/phpunit-testlistener-xhprof": "dev-master"
    }
}
```

Update the vendor libraries

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

Usage
-----
Just add to your `phpunit.xml` configuration
```xml
<phpunit>
    <listeners>
        <listener class="PHPUnit_Util_Log_XHProf">
            <arguments>
                <array>
                    <element key="xhprofLibFile">
                        <string>/var/www/xhprof_lib/utils/xhprof_lib.php</string>
                    </element>
                    <element key="xhprofRunsFile">
                        <string>/var/www/xhprof_lib/utils/xhprof_runs.php</string>
                    </element>
                    <element key="xhprofWeb">
                        <string>http://localhost/xhprof_html/index.php</string>
                    </element>
                    <element key="xhprofFlags">
                        <string>XHPROF_FLAGS_CPU,XHPROF_FLAGS_MEMORY</string>
                    </element>
                    <element key="xhprofIgnore">
                        <string>call_user_func,call_user_func_array</string>
                    </element>
                    <element key="appNamespace">
                        <string>MyNamespace</string>
                    </element>
                </array>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```
