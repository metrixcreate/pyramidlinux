Installing Pyramid Linux can be done a couple of different ways. Installation instructions assume you are installing on an embedded platform such as a Soekris board either via copying an image to compact flash (easiest method if available) or by using PXE.

If you have Soekris board with non-removable flash, PXE network based installation is your only option unless you have an existing OS running on it. PXE Network installation can be complicated and requires many components (http server, tftp server, dhcp server). To make this easier Metrix has produced a Live CD image with a complete PXE environment that you can boot on a laptop or PC. Simply booting this Live CD and running one command, you should have everything up and running to install or upgrade Pyramid on your system. If you have an existing running OS, you can [wiki:InstallingPyramid/UpgradingImg Upgrade an existing install with a .img.gz]

[PXE Installation with Metrix Live CD](PxeBootLiveCD.md)

[PXE Installation](PxeBoot.md)

[PXE Installation from Windows](PxeBootWin.md)

[Compact Flash Card Installation using DD in Linux or BSD](DD.md)

[Compact Flash Installation using PhysDiskWrite on Windows](PhysDiskWrite.md)

[Upgrading an existing install with a .img.gz](UpgradingImg.md)