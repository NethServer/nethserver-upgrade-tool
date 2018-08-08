nethserver-upgrade-tool
=======================

This procedure is based on ``redhat-upgrade-tool`` [#rht]_ and upgrades NethServer 6 to 7
in three steps:

1. (preparation) downloads all required RPMs from a set of special repositories

2. (upgrade) at next reboot runs the RPMs upgrade transaction, the upgrade 
   tasks, then reboots

3. (post-upgrade) completes by running post-restore-config and post-restore-data
   events

This procedure returns a NethServer 7 installation, retaining

- grub boot loader installation and configuration
- previous partitioning, RAID and (ext4) file systems
- LDAP accounts provider - if present

All the installed RPMs are upgraded/removed to conform to a NethServer 7
installation. Access to Enterprise repositories is preserved, where applicable.

Run the upgrade procedure
-------------------------

1. Run the preparation step and activate the upgrade: ``signal-event
   nethserver-upgrade-tool-prepare``

2. Reboot to run the upgrade and post-upgrade steps

To deactivate the upgrade procedure and revert the preparation step, run ::

    redhat-upgrade-tool --clean


Configuration database
----------------------

::

    upgrade-tool=configuration
        RepoSet=ug7

* ``RepoSet``, the YUM repositories name prefix for the upgrade

YUM repositories for the upgrade
--------------------------------

The ``RepoSet`` prop selects the YUM repositories for the upgrade. Its value is
used as glob-prefix for the names of YUM repositories. The default ``ug7``
prefix actually  enables the following repositories, defined in
``/etc/yum.repos.d/ug7repos.repo``:

- ug7-instrepo
- ug7-base
- ug7-updates
- ug7-extras
- ug7-centos-sclo-sclo
- ug7-centos-sclo-rh
- ug7-epel
- ug7-nethforge
- ug7-nethserver-base
- ug7-nethserver-updates

The first one, ``ug7-instrepo`` is used to retrieve the Linux kernel and initrd
file that run the upgrade (see the dedicated section below). The other
repositories are pointers to ns7 official repositories. All required GPG keys
are imported from ::

    /etc/pki/rpm-gpg/RPM-GPG-KEY-ug7bundle


Customize the YUM repositories set
----------------------------------

To provide a different set of repositories:

1. Add a ``.repo`` file to ``/etc/yum.repos.d/``

2. Set the ``RepoSet`` prop to a custom prefix (e.g. "my")

3. Add the GPG keys to ``/etc/pki/rpm-gpg/RPM-GPG-KEY-mybundle``


Upgrade procedure workflow
--------------------------

The **preparation step** is implemented by the ``nethserver-upgrade-tool-prepare``
event.  It runs ``redhat-upgrade-tool``, to download the new Linux kernel
and initrd from the "instrepo" repository, then downloads the new RPMs from
other enabled repositories, selected by the ``RepoSet`` prop value.

``redhat-upgrade-tool`` also configures GRUB to boot the new kernel and the
initrd upgrade image on the next reboot (see the section below). It customizes
the initrd image with the local RAID setup, required to mount the root device.

After ``redhat-upgrade-tool`` completes successfully, the ``backup-config``
command and ``pre-backup-data`` event are executed. The enabled repositories are
contacted to download the remaining RPMs and their dependencies, as required by
NethServer and defined by the following lists:

- "centos-minimal" YUM group
- "nethserver-iso" YUM group
- ``packages-list`` from backup-config

All RPMs are downloaded into ``/var/lib/system-upgrade``.

The next reboot starts the system **upgrade step**, implemented by the
``redhat-upgrade-dracut`` [#rhd]_ component. It installs the downloaded RPMs and erases
the old ones, then it runs any executable file under
``/root/preupgrade/postupgrade.d/``. The ``nethserver`` executable, provided by
``nethserver-upgrade-tool`` actually performs the post RPM transaction
adjustments.

The system is automatically rebooted and the **post-upgrade step** starts. The
control passes to the ``nethserver-system-upgrade.service`` temporary systemd
unit, that actually renames the network interfaces in esmith DBs, then runs
``post-restore-config`` and ``post-restore-data`` steps.

Build the "instrepo" repository
-------------------------------

In a (clean and) up-to-date NethServer 7 installation run the following
commands, to generate the initrd image required by the upgrade procedure.

Many dracut modules are provided by the ``dracut`` package, but they are
disabled because some binary tools are missing: they must be installed
manually. The resulting image must contain enough modules to boot any kind
of device out there.

(1) install prerequisites ::

        yum install redhat-upgrade-dracut dracut-fips dracut-network cryptsetup mdadm dmraid device-mapper-multipath fcoe-utils iscsi-initiator-utils
        sed 's/plymouth-label//g' $(rpm -qd redhat-upgrade-dracut | grep make-redhat-upgrade-repo) > make-redhat-upgrade-repo

(2) create output directory and generate the initrd ::

        mkdir ns6upgrade/
        bash make-redhat-upgrade-repo ns6upgrade

Copy the ``ns6upgrade/`` contents to a public web server.


Set upgrade breakpoints
-----------------------

Once rebooted, the upgrade step can be stopped at certain points, as documented
in the ``redhat-upgrade-dracut`` repository. 

For instance, to break at the ``upgrade-post`` hook  edit the kernel parameters
at the grub prompt and add ``rd.break=upgrade-post``. Adding that parameter
seems to activate the "pre chroot switch" break point implicitly.

When the procedure stops at a break point a shell is spawned. The procedure
continues when that shell is closed.


----

.. rubric:: Footnotes

.. [#rht] https://github.com/NethServer/redhat-upgrade-tool
.. [#rhd] https://github.com/upgrades-migrations/redhat-upgrade-dracut