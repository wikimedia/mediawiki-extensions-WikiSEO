<?php
$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

// Ignored to allow upgrading Phan, to be fixed later.
$cfg['suppress_issue_types'][] = 'MediaWikiNoBaseException';
$cfg['suppress_issue_types'][] = 'MediaWikiNoEmptyIfDefined';

$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'../../extensions/PageImages',
		'../../extensions/Scribunto',
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'../../extensions/PageImages',
		'../../extensions/Scribunto',
	]
);

return $cfg;
