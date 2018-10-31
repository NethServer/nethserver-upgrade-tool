Name:           nethserver-upgrade-tool
Version: 1.0.0
Release: 1%{?dist}
Summary:        NethServer upgrade tool

License:        GPLv3
URL:            %{url_prefix}/%{name}
Source0:        %{name}-%{version}.tar.gz

BuildRequires:  nethserver-devtools
Requires:       redhat-upgrade-tool
Requires:       nethserver-backup-config
Requires:       yum-utils

%description
Upgrade NethServer to the next major version

%prep
%setup

%build
%{makedocs}
perl createlinks


%install
rm -rf %{buildroot}
(cd root   ; find . -depth -print | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} > %{name}-%{version}-filelist


%files -f %{name}-%{version}-filelist
%doc COPYING
%doc README.rst

%changelog
* Wed Oct 31 2018 Davide Principi <davide.principi@nethesis.it> - 1.0.0-1
- Live upgrade procedure - Feature #3446
- Live upgrade procedure - NethServer/dev#5564

* Mon Jul 30 2018 Davide Principi <davide.principi@nethesis.it> - 0.0.0
- Initial version
