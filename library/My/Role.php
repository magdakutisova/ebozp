<?php
class My_Role{
	
	const ROLE_ADMIN = 1;
	const ROLE_COORDINATOR = 2;
	const ROLE_TECHNICIAN = 3;
	const ROLE_CLIENT = 4;
	
	private static $roles = array(
		self::ROLE_ADMIN => 'Admin',
		self::ROLE_COORDINATOR => 'Koordinátor',
		self::ROLE_TECHNICIAN => 'Bezpečnostní technik',
		self::ROLE_CLIENT => 'Klient'
	);
	
	public static function getRoles(){
		return self::$roles;
	}
	
	public static function getRoleName($role){
		if (isset(self::$roles[$role])){
			return self::$roles[$role];
		}		
	}
}