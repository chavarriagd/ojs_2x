<?php

/**
 * @file plugins/citationOutput/vancouver/VancouverCitationOutputPlugin.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class VancouverCitationOutputPlugin
 * @ingroup plugins_citationOutput_vancouver
 *
 * @brief Vancouver citation style plug-in.
 */


import('lib.pkp.plugins.citationOutput.vancouver.PKPVancouverCitationOutputPlugin');

class VancouverCitationOutputPlugin extends PKPVancouverCitationOutputPlugin {
	/**
	 * Constructor
	 */
	function VancouverCitationOutputPlugin() {
		parent::PKPVancouverCitationOutputPlugin();
	}
}

?>
