<?php


namespace Octfx\WikiSEO\Generator;

use OutputPage;

interface GeneratorInterface
{

	public function addMetaToPage(OutputPage $out);
}