Knowledge_Base
==============

An extension for phpBB 3.1.x which allows an administrator to integrate a Knowledge Base system.

## Install
You can install this on the latest copy of the develop branch ([phpBB 3.1-dev](https://github.com/phpbb/phpbb3)) by following the steps below:

1. [Download the latest release](https://github.com/tmbackoff/Knowledge_Base/releases).
2. Unzip the downloaded release, and change the name of the folder to `knowledgebase`.
3. In the `ext` directory of your phpBB board, create a new directory named `tmbackoff` (if it does not already exist).
4. Copy the `knowledgebase` folder to `/ext/tmbackoff/` (if done correctly, you'll have the main extension class at `/ext/tmbackoff/knowledgebase/ext.php`).
5. Navigate to `CUSTOMISE -> EXTENSION MANAGEMENT -> Manage extensions`.
6. Look for `Knowledge Base` under the Disabled Extensions list, and click the `Enable` link.
7. Set up and configure the extension.

## Uninstall

1. Navigate to `CUSTOMISE -> EXTENSION MANAGEMENT -> Manage extensions`.
2. Look for `Knowledge Base` under the Enabled Extensions list, and click the `Disable` link.
3. To permanently uninstall, click the `Delete data` link and delete the `/ext/tmbackoff/knowledgebase` folder.

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)