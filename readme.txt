###################################
Licence : 
	You must have read and agreed with "licence.txt" is include in my package.
	
	This module is based on Invision Power Board version 1.x (IPS, INC owner at wwww.invisionboard.com), 
	Because I have modifed/edited IPB v1.x become a module for XOOPS portal, IPS INC will not have any responsible for any bugs and errors if it occurs.
	Please! Don't ask them any things concern with my module. So that, using my module with your own risk.
	
	My module is legal with the permission of IPS, INC and it is only distribued from my site.
	Please! Don't distribute my module if you don't know what are you doing.
___________________________________		
###################################
____ Module name: IPBM
____ Compatibility:  XOOPS V2.0
____ Version : 1.1.3
____ Release : 16-Apr-2003
____ Author  : Koudanshi
____ Homepage: http://koudanshi.net

-----------------------------------
Update: 	
/---------------------
|Version 1.1.3
\---------------------
16-Apr-2003		Update to new IPB oringin 1.1.2
			Compatibility with XOOPS 2.0 Final
	
/---------------------
|Version 1.1.2
\---------------------
23-Mar-2003		Fix Avatar not show in Blocks (Top_members + New_members + Info_admin)
			Fix bugs why go to root when you in ipboard and login from XOOPS user menu.
/---------------------
|Version 1.1.1
\---------------------
19-Mar-2003		Update for compatibility with XOOPS2 RC3
03-Mar-2003		Fix Avatar not show if use from other host.
			Fix auto redirect bug when login by IPBM.
25-Feb-2003		Add auto reinstall function.
			Fix UserName don't list in Forum Leader.
			Fix afew session.
24-Feb-2003		Update to 1.1a
09-Feb-2003 		Auto redirect to last location after login.
			Fixed IPB sessions to full compatibility with XOOPS.
06-Feb-2003		Compatibility: XOOPS 2.0
			Can use both registration of XOOPS of IPB.
			Can login in both XOOPS or IPB.
			Can use any Skins at IBSkin.com for IPB 1.1 origin.
			Can use hack/mods which was show at my site.
			Fixed Avatar size error in IPB origin.	
			And more...

	03-Sep-2002: Fix PM reply not auto get uname from.
		         Change PM from userinfo --> full screen
	28-Sep-2002: Fix blank page after installed.
			 Fix keep smiles from original of user.
	11-Sep-2002: Fix login block.
	20-Aug-2002: Fix sort by joined, moderate CP post topic
	19-Aug-2002: Fix can't view emoticon, user name, avatar.
			 Fix member list at on both admin group link and global.
			 Fix temper user "Guest" can view in XOOPS admin.
			 Fix avatar can't view when register (bug by origin IBF).			 
	16-Aug-2002: Fix bugs edit users in Admin CP.

===================================
    ---- Tutorial: Install ----
-----------------------------------
1. You copy overwritten all of content inside "IPB_module_1.x" folder (include subs dir) into "yoursite.com/Xoops_folder/" .

Trees dir discrible:
/___________________________________________________\
|---------------------------------------------------
|
|    +-- IPB_module_1.1   Copy to    +-- XOOPS 
|	+-- ..				+--..
|	+-- include			+-- include
|	+-- kernel			+-- kernel
|	+-- modules			+-- modules
|	+-- uploads			+-- uploads
|
|____________________________________________________
\ --------------------------------------------------/

2. Login as XOOPS AdminCP and active this module.
3. Goto forum.
4. Install as tutorial.
5. Finish.
-----------------------------------

===================================
    ---- Tutorial: Upgrade ----
-----------------------------------

1. If you are running IBFM 1.01 
	When you install IPBM 1.x, you only need choose "Upgrade from IBF 1.01".

2. If you are running IBFM 1.02 b2
	After active IPBM 1.x module, don't go to Message board.
	You only need:
		Copy files "install.lock" and "config_global.php" of IBFM 1.02 over wrriten to "ipboard" dir.
		Change prefix of IPBM tables from"_ibf_" to "_ipb_" (by phpmyadmin).
		Delete file sm_install.php for security.
		Tree dir:
		+-- XOOPS/modules/forum/	Copy to	    +-- XOOPS/modules/ipboard/
			+-- install.lock			+-- ..
			+-- config_global.php 			+-- ..
								