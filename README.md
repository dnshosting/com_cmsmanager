CMS Manager
===========

![Logo CMS Manager](https://cdn.colt-engine.it/files/joomla/com_cmsmanager/cmsmanager.jpg)

Il CMS Manager (http://www.joomlahost.it/dnshst/jm/cms-manager.jsp) è un componente sviluppato per Joomla! che permette di gestire tutti i siti costruiti con il CMS 
dalla versione 2.5 in avanti. 
La gestione avviene tramite un unico Pannello di Controllo al quale si possono aggiungere i siti da gestire, 
compatibile con tutti gli hosting service provider.
Il componente esegue la scansione dei siti e delle estensioni presenti e mostra all'utente se ci sono aggiornamenti da effettuare. 
Grazie al sistema di notifiche mail, l'utente riceve la comunicazione degli aggiornamenti disponibili e può decidere se aggiornare 
tutto con un semplice click oppure selezionare le singole voci di suo interesse.
In questo modo tutti i siti in Joomla! che utilizzano il CMS Manager saranno sempre aggiornati e protetti in pochi semplici passaggi.

CMS Manager (http://www.joomlahost.it/dnshst/jm/cms-manager.jsp) is a component developed specifically for Joomla! that allows you to manage all websites developed with this CMS (starting from version 2.5). 
Thanks to its Control Panel, CMS Manager manages all Joomla! websites added and it is compatible with all hosting service providers.
CMS Manager scans all websites and extension installed in all websites and show the users if there are any updates. 
Thanks to an e-mail notification system, users is always informed about updates. It is also possible to update all websites and 
extensions with one click or simply select some of the items to update.
In this way all Jooomla! websites that use CMS Manager can be always updated and protected with few clicks.

![Screenshot Joomla 3.x](https://cdn.colt-engine.it/files/joomla/com_cmsmanager/screen-cmsmanager.png)

Start the CMS Manager Development
---------------------------------

In order to contribute to this project

1. Install [VirtualBox](http://www.virtualbox.org/)
2. Install [Vagrant](http://www.vagrantup.com/)
3. Run the following commands to run the vagrant-box:

```bash
vagrant plugin install vagrant-hostsupdater
vagrant up
```

4. The administration dashboard is available at [joomla.box](http://joomla.box)

The `www` and `Projects` folders act as shared folders between your host computer and the box.

Once you have installed the box as described above, you can SSH into the box with

```bash
vagrant ssh
```

And create different Joomla installations with the CMS Manager preinstalled using the joomla cli

```
joomla site:create j25 --symlink=cmsmanager --joomla=2.5
joomla extension:install j25 com_cmsmanager
```

The site will be available at [joomla.box/j25](http://joomla.box/j25).

Contribute
----------

Contributions are welcome.

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request