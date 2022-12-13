<?php

///////////////////////////////////////////////////////////////////////////////
// @author	Steve Kraemer
// @address	
// @created	2015-12-16
// @info		The mother class for all modules.
//					A module is only used as module, if it's class is derived by this
//					base class.
// @license	All rights reserved.
//////////////////////////////////////////////////////////////////////////////

class cModule {
		public static function setBootHooks() {
				
		}
		
		public static function boot() {
			
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns an array of all modules.
		//
		// Return Value Array Shema:
		//		array(
		//				array(
		//						'module' => 'path/and/module/name',
		//						'version' => '1.6',		//Minimum version of dependent module that is needed to run this module.
		//						'required' => false		//If value is set and is set to true
		//																	//the system ends with a fatal error,
		//																	//if the required module is not available.
		//				), 
		//				array(..)
		//		);
		//
		//		The systems core logic checks all dependencies in the auto loader.
		//			
		//////////////////////////////////////////////////////////////////////////
		public static function getDependenciesAsArray() {
				return array();
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns the version of a module.
		// Returns 0 (zero) if you define no version for your module.
		//////////////////////////////////////////////////////////////////////////
		public static function getVersion() {
				return 0;
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Use this function to set core hooks.
		// Core hooks are used, to implement basic functionality,
		// that should be availlable on every single page/resource.
		/////////////////////////////////////////////////////////////////////////
		public static function setCoreHooks() {
				return 0;
		}
		
		////////////////////////////////////////////////////////////////////////
		// Use this function to set the controlling hooks.
		// This hooks are only installed, if the module is called via the
		// execution parameter s.
		// This means, with placing hooks in this function,
		// you allow a module to be used as controller.
		////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				return 0;	
		}
		
		////////////////////////////////////////////////////////////////////////
		// Use this function to set additional hooks.
		// This is a basic feature for upgrading/changing other modules.
		// It allows to install different functionality.
		// May it be before or after core modules (which allowes extending core functionality),
		// before or after executional hooks (which allowes extending controllers),
		// and even before and after additional hooks (which allowes almost endles customization).
		//
		// Be warned! You can only use one (1) combination of class an function in the list.
		// The execution of this function is recursive.
		// Because of this fact - the function checks, if a combination of class|hook
		// is already present. If it finds one, it won't install it again -
		// even if you want to install it in another parent hook.
		// You would have to use an alias.
		//
		// We are thinking about changing this feature, so the same class|hook combination would be
		// possible for different parent hooks - but we even think it is easier to manage
		// projects with the actual state.
		// So we won't change it by now.
		////////////////////////////////////////////////////////////////////////
		public static function setAdditionalHooks() {
				return 0;
		}
}

?>