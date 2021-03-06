<?php

/**
 * @file plugins/citationLookup/pubmed/PubmedCitationLookupPlugin.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PubmedCitationLookupPlugin
 * @ingroup plugins_citationLookup_pubmed
 *
 * @brief PubMed citation database connector plug-in.
 */


import('lib.pkp.plugins.citationLookup.pubmed.PKPPubmedCitationLookupPlugin');

class PubmedCitationLookupPlugin extends PKPPubmedCitationLookupPlugin {
	/**
	 * Constructor
	 */
	function PubmedCitationLookupPlugin() {
		parent::PKPPubmedCitationLookupPlugin();
	}
}

?>
