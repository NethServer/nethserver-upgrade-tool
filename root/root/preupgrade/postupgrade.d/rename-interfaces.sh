#!/bin/bash

#
# Copyright (C) 2018 Nethesis S.r.l.
# http://www.nethesis.it - nethserver@nethesis.it
#
# This script is part of NethServer.
#
# NethServer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License,
# or any later version.
#
# NethServer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NethServer.  If not, see COPYING.
#

# !!! DO NOT SET THE EXECUTABLE BIT ON THIS FILE !!!
# It is passed to a bash binary in nethserver-system-upgrade.service unit
# to avoid executing it in the upgrade initrd


args_list=()

declare -A old_map
declare -A new_map

while IFS=, read name mac rest; do
    old_map["${mac}"]="${name}"
done </root/preupgrade/postupgrade.d/nicinfo_old.list

while IFS=, read name mac rest; do
    new_map["${mac}"]="${name}"
done < <(/usr/libexec/nethserver/nic-info)

for mac in "${!new_map[@]}"; do
    newname="${new_map[${mac}]}"
    oldname="${old_map[${mac}]}"
    if [[ -n "${oldname}" && -n "${newname}" && "${oldname}" != "${newname}" ]]; then
        echo "[NOTICE] Mapping interface ${oldname} to ${newname}"
        args_list+=("${oldname}" "${newname}")
    else
        echo "[NOTICE] Interface ${newname}/${mac} skipped"
    fi
done

exec /etc/e-smith/events/actions/interface-rename system-upgrade ${args_list[*]}

