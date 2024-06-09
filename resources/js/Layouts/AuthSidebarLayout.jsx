import '../../css/app.css';

import { useState } from "react";
import ApplicationLogo from "@/Components/ApplicationLogo";
import NavLink from "@/Components/NavLink";
import { Link } from "@inertiajs/react";
import { HomeIcon, BriefcaseIcon, UserGroupIcon, UserCircleIcon, ArrowLeftEndOnRectangleIcon, ClipboardDocumentIcon, Cog6ToothIcon, ChevronDownIcon, BuildingOffice2Icon } from '@heroicons/react/24/outline';

export default function AuthSidebarLayout({ user, header, children }) {
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    return (
        <div className="h-dvh flex flex-row bg-gray-100">
            <div id="navigation" className="flex flex-col w-80 bg-white rounded-r-3xl overflow-hidden">
                <div className="flex items-center justify-center h-20 shadow-md">
                    <Link href="/">
                        <ApplicationLogo className="h-10" />
                    </Link>
                </div>
                <div className="flex-grow overflow-y-auto">
                    <div className="shrink-0 flex items-center mb-8">
                        <div className="dropdown w-full border border-gray-400">
                            <div
                                tabIndex="0"
                                role="button"
                                className="btn justify-between w-full bg-gray-100">
                                {user?.team_name}
                                <ChevronDownIcon className="h-4 w-4" />
                            </div>
                            {/* <ul tabIndex="0"
                                className="dropdown-content z-[1] menu shadow bg-base-100 rounded-box">
                                <li><a>Team 1</a></li>
                                <li><a>Team 2</a></li>
                            </ul> */}
                        </div>
                    </div>
                    <ul className="flex flex-col py-4">
                        {/* <li>
                            <div className="shrink-0 flex items-center mb-8">
                                <NavLink href={route('dashboard')} active={route().current('dashboard')}>
                                    <span className="inline-flex items-center justify-center h-6 w-6 text-lg m-2 "><HomeIcon /></span>
                                    <span className="text-xl font-medium">Dashboard</span>
                                </NavLink>
                            </div>
                        </li> */}
                        <li>
                            <div className="shrink-0 flex items-center mb-8">
                                <NavLink href={route('filing.index')} active={route().current('filing.*')}>
                                    <span className="inline-flex items-center justify-center h-6 w-6 text-lg m-2 "><BriefcaseIcon /></span>
                                    <span className="text-xl font-medium">Filings</span>
                                </NavLink>
                            </div>
                        </li>
                        <li>
                            <div className="shrink-0 flex items-center mb-8">
                                <NavLink href={route('owners.index')} active={route().current('owners.*')} >
                                    <span className="inline-flex items-center justify-center h-6 w-6 text-lg ml-2 mr-2 "><BuildingOffice2Icon /></span>
                                    <span className="text-xl font-medium">Owners</span>
                                </NavLink>
                            </div>
                        </li>
                        <li>
                            <div className="shrink-0 flex items-center mb-8">
                                <NavLink href={route('applicants.index')} active={route().current('applicants.*')} >
                                    <span className="inline-flex items-center justify-center h-6 w-6 text-lg ml-2 mr-2 "><ClipboardDocumentIcon /></span>
                                    <span className="text-xl font-medium">Applicants</span>
                                </NavLink>
                            </div>
                        </li>
                    </ul>

                    {(user?.role_name === 'administrator' || user?.role_name === 'superadministrator') && (
                        <>
                            <div className="divider">Manage Team</div>
                            <ul className="flex flex-col py-4">
                                {/* <li>
                                    <div className="shrink-0 flex items-center mb-8">
                                        <NavLink href={route('team.info.index')} active={route().current('team.info.*')}>
                                            <span className="inline-flex items-center justify-center h-6 w-6 text-lg ml-2 mr-2 "><Cog6ToothIcon /></span>
                                            <span className="text-xl font-medium">Info</span>
                                        </NavLink>
                                    </div>
                                </li> */}
                                <li>
                                    <div className="shrink-0 flex items-center mb-8">
                                        <NavLink href={route('team.members.index')} active={route().current('team.members.*')}>
                                            <span className="inline-flex items-center justify-center h-6 w-6 text-lg ml-2 mr-2 "><UserGroupIcon /></span>
                                            <span className="text-xl font-medium">Members</span>
                                        </NavLink>
                                    </div>
                                </li>
                            </ul>
                        </>
                    )}
                </div>
                <div id="profile" className="mt-auto">
                    <div className="flex justify-between p-4">
                        <ul className="flex flex-col py-4">
                            <li>
                                <div className="shrink-0 flex items-center mb-8">
                                    <NavLink href={route('profile.edit')} active={route().current('profile.edit')}>
                                        <span className="inline-flex items-center justify-center h-6 w-6 text-lg ml-2 mr-2 "><UserCircleIcon /></span>
                                        <span className="text-xl font-medium">Profile</span>
                                    </NavLink>
                                </div>
                            </li>
                            <li>
                                <div className="shrink-0 flex items-center">
                                    <NavLink href={route('logout')} method="post" as="button">
                                        <span className="inline-flex items-center justify-center h-6 w-6 text-lg ml-2 mr-2 "><ArrowLeftEndOnRectangleIcon /></span>
                                        <span className="text-xl font-medium">Logout</span>
                                    </NavLink>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="contents" className="flex flex-col flex-1 overflow-y-auto">
                <div className="p-4">
                    {children}
                </div>
            </div>
        </div>
    )
}