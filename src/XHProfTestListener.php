<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\XHProfTestListener;

/**
 * A TestListener that integrates with XHProf.
 *
 * Here is an example XML configuration for activating this listener:
 *
 * <code>
 * <listeners>
 *  <listener class="PHPUnit\XHProfTestListener\XHProfTestListener">
 *   <arguments>
 *    <array>
 *     <element key="xhprofLibFile">
 *      <string>/var/www/xhprof_lib/utils/xhprof_lib.php</string>
 *     </element>
 *     <element key="xhprofRunsFile">
 *      <string>/var/www/xhprof_lib/utils/xhprof_runs.php</string>
 *     </element>
 *     <element key="xhprofWeb">
 *      <string>http://localhost/xhprof_html/index.php</string>
 *     </element>
 *     <element key="appNamespace">
 *      <string>Doctrine2</string>
 *     </element>
 *     <element key="xhprofFlags">
 *      <string>XHPROF_FLAGS_CPU,XHPROF_FLAGS_MEMORY</string>
 *     </element>
 *     <element key="xhprofIgnore">
 *      <string>call_user_func,call_user_func_array</string>
 *     </element>
 *    </array>
 *   </arguments>
 *  </listener>
 * </listeners>
 * </code>
 *
 * @author     Benjamin Eberlei <kontakt@beberlei.de>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2011-2015 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.0.0
 */
class XHProfTestListener implements \PHPUnit_Framework_TestListener
{
    /**
     * @var array
     */
    protected $runs = array();

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var integer
     */
    protected $suites = 0;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (!isset($options['appNamespace'])) {
            throw new InvalidArgumentException(
              'The "appNamespace" option is not set.'
            );
        }

        if (!isset($options['xhprofLibFile']) ||
            !file_exists($options['xhprofLibFile'])) {
            throw new InvalidArgumentException(
              'The "xhprofLibFile" option is not set or the configured file does not exist'
            );
        }

        if (!isset($options['xhprofRunsFile']) ||
            !file_exists($options['xhprofRunsFile'])) {
            throw new InvalidArgumentException(
              'The "xhprofRunsFile" option is not set or the configured file does not exist'
            );
        }

        require_once $options['xhprofLibFile'];
        require_once $options['xhprofRunsFile'];

        $this->options = $options;
    }

    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if (!isset($this->options['xhprofFlags'])) {
            $flags = XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY;
        } else {
            $flags = 0;

            foreach (explode(',', $this->options['xhprofFlags']) as $flag) {
                $flags += constant($flag);
            }
        }

        xhprof_enable($flags, array(
            'ignored_functions' => explode(',', $this->options['xhprofIgnore'])
        ));
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $data         = xhprof_disable();
        $runs         = new XHProfRuns_Default;
        $run          = $runs->save_run($data, $this->options['appNamespace']);
        $this->runs[$test->getName()] = $this->options['xhprofWeb'] . '?run=' . $run .
                                        '&source=' . $this->options['appNamespace'];
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->suites++;
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->suites--;

        if ($this->suites == 0) {
            print "\n\nXHProf runs: " . count($this->runs) . "\n";

            foreach ($this->runs as $test => $run) {
                print ' * ' . $test . "\n   " . $run . "\n\n";
            }

            print "\n";
        }
    }
}
