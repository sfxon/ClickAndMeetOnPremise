<?php

class cToken extends cModule {
		/////////////////////////////////////////////////////////////////////
		// Generate 256 bit token.
		/////////////////////////////////////////////////////////////////////
		public function generate() {
				return hash('sha256', time() . rand());
		}
}