import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
                <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
                    <nav className="flex items-center justify-end gap-4">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                >
                                    Log in
                                </Link>
                                {canRegister && (
                                    <Link
                                        href={register()}
                                        className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                    >
                                        Register
                                    </Link>
                                )}
                            </>
                        )}
                    </nav>
                </header>
                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <div className="w-full max-w-6xl px-6 py-12 lg:px-8">
                        {/* Hero Section */}
                        <div className="mb-16 text-center">
                            <h1 className="mb-4 text-4xl font-bold text-[#1b1b18] lg:text-6xl dark:text-[#EDEDEC]">
                                Clinic Billing + Scheduling System
                            </h1>
                            <p className="mx-auto mb-8 max-w-3xl text-lg text-[#706f6c] lg:text-xl dark:text-[#A1A09A]">
                                A comprehensive Laravel + React solution for
                                modern medical clinics, featuring advanced
                                patient management, intelligent scheduling, and
                                robust billing capabilities.
                            </p>
                            <div className="flex flex-col justify-center gap-4 sm:flex-row">
                                <Link
                                    href={login()}
                                    className="inline-block rounded-sm border border-[#19140035] px-6 py-3 text-sm leading-normal font-medium text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                >
                                    Get Started
                                </Link>
                                {canRegister && (
                                    <Link
                                        href={register()}
                                        className="inline-block rounded-sm border border-[#19140035] px-6 py-3 text-sm leading-normal font-medium text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                    >
                                        Create Account
                                    </Link>
                                )}
                            </div>
                        </div>

                        {/* Features Sections */}
                        <div className="mb-16 grid grid-cols-1 gap-12 lg:grid-cols-2">
                            {/* Patient Management */}
                            <div className="rounded-lg bg-white p-8 shadow-lg dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                                <h2 className="mb-6 text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    🧑‍⚕️ Patient Management
                                </h2>
                                <ul className="space-y-3 text-[#706f6c] dark:text-[#A1A09A]">
                                    <li>
                                        • Comprehensive patient profiles with
                                        medical history
                                    </li>
                                    <li>
                                        • Document attachment support (PDF,
                                        images)
                                    </li>
                                    <li>• Visit history tracking</li>
                                    <li>
                                        • Advanced search by phone, name, or ID
                                    </li>
                                    <li>
                                        • Blood group, allergies, and chronic
                                        conditions tracking
                                    </li>
                                </ul>
                            </div>

                            {/* Doctor Management */}
                            <div className="rounded-lg bg-white p-8 shadow-lg dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                                <h2 className="mb-6 text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    👨‍⚕️ Doctor Management
                                </h2>
                                <ul className="space-y-3 text-[#706f6c] dark:text-[#A1A09A]">
                                    <li>
                                        • Weekly schedule availability
                                        management
                                    </li>
                                    <li>
                                        • Consultation types (online/offline)
                                    </li>
                                    <li>• Custom fees per service</li>
                                    <li>• Leave management system</li>
                                </ul>
                            </div>

                            {/* Appointments */}
                            <div className="rounded-lg bg-white p-8 shadow-lg dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                                <h2 className="mb-6 text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    📅 Appointment System
                                </h2>
                                <ul className="space-y-3 text-[#706f6c] dark:text-[#A1A09A]">
                                    <li>• Time-slot based booking</li>
                                    <li>• Doctor-wise calendar views</li>
                                    <li>• Auto-slot generation</li>
                                    <li>• Waiting list management</li>
                                    <li>• Automated appointment reminders</li>
                                    <li>• Walk-in appointment support</li>
                                    <li>• Configurable overbooking rules</li>
                                </ul>
                            </div>

                            {/* Billing & Finance */}
                            <div className="rounded-lg bg-white p-8 shadow-lg dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                                <h2 className="mb-6 text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    💰 Billing & Finance
                                </h2>
                                <ul className="space-y-3 text-[#706f6c] dark:text-[#A1A09A]">
                                    <li>• Multiple services per visit</li>
                                    <li>• Medicine and lab test charges</li>
                                    <li>• Discounts and taxes calculation</li>
                                    <li>• Insurance support</li>
                                    <li>• Refunds and adjustments</li>
                                    <li>• Comprehensive invoice generation</li>
                                </ul>
                            </div>

                            {/* Medical Records */}
                            <div className="rounded-lg bg-white p-8 shadow-lg dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                                <h2 className="mb-6 text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    📋 Medical Records
                                </h2>
                                <ul className="space-y-3 text-[#706f6c] dark:text-[#A1A09A]">
                                    <li>• Doctor consultation notes</li>
                                    <li>• Prescription management</li>
                                    <li>• Diagnosis tracking</li>
                                    <li>• Follow-up scheduling</li>
                                </ul>
                            </div>

                            {/* Reports & Analytics */}
                            <div className="rounded-lg bg-white p-8 shadow-lg dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                                <h2 className="mb-6 text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    📊 Reports & Analytics
                                </h2>
                                <ul className="space-y-3 text-[#706f6c] dark:text-[#A1A09A]">
                                    <li>
                                        • Financial reports (revenue, earnings,
                                        payments)
                                    </li>
                                    <li>
                                        • Operational metrics (no-show rates,
                                        utilization)
                                    </li>
                                    <li>• Appointment cancellation analysis</li>
                                    <li>• Export capabilities (CSV/PDF)</li>
                                </ul>
                            </div>
                        </div>

                        {/* System Capabilities */}
                        <div className="rounded-lg bg-white p-8 shadow-lg dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                            <h2 className="mb-6 text-center text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                🏥 System Capabilities
                            </h2>
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <h3 className="mb-2 font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Role-Based Access
                                    </h3>
                                    <p className="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        Super Admin, Clinic Admin, Receptionist,
                                        Doctor, Accountant roles
                                    </p>
                                </div>
                                <div>
                                    <h3 className="mb-2 font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Multi-Clinic Support
                                    </h3>
                                    <p className="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        Manage multiple clinics with shared or
                                        separate data
                                    </p>
                                </div>
                                <div>
                                    <h3 className="mb-2 font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Security & Compliance
                                    </h3>
                                    <p className="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        Audit logs, data encryption, activity
                                        tracking
                                    </p>
                                </div>
                                <div>
                                    <h3 className="mb-2 font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Notifications
                                    </h3>
                                    <p className="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        Email, SMS, WhatsApp reminders and
                                        alerts
                                    </p>
                                </div>
                                <div>
                                    <h3 className="mb-2 font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Modern Tech Stack
                                    </h3>
                                    <p className="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        Laravel API + React frontend with
                                        TypeScript
                                    </p>
                                </div>
                                <div>
                                    <h3 className="mb-2 font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Queue Processing
                                    </h3>
                                    <p className="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        Background jobs for notifications and
                                        reports
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </>
    );
}
