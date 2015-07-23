# -*- mode: ruby -*-
# vi: set ft=ruby :

unless Vagrant.has_plugin?("vagrant-hostsupdater")
  raise 'vagrant-hostsupdater is not installed!'
end

$script = <<SCRIPT
  echo "Symlinking the CMS Manager directories into the Projects/ directory"
  mkdir -p /home/vagrant/Projects/cmsmanager/administrator/components
  ln -sfn /vagrant/src/admin /home/vagrant/Projects/cmsmanager/administrator/components/com_cmsmanager
  mkdir -p /home/vagrant/Projects/cmsmanager/components
  ln -sfn /vagrant/src/site /home/vagrant/Projects/cmsmanager/components/com_cmsmanager
  mkdir -p /home/vagrant/Projects/cmsmanager/media
  ln -sfn /vagrant/src/assets /home/vagrant/Projects/cmsmanager/media/com_cmsmanager
SCRIPT


# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  config.vm.box = "joomlatools/box"

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Map the hostname and the host aliases to the /etc/hosts file
  config.vm.hostname = "joomla.box"
  config.hostsupdater.aliases = ["webgrind.joomla.box", "phpmyadmin.joomla.box"]

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant.
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #   vb.memory = "1024"
  # end

  config.vm.provision "shell", inline: $script

end
