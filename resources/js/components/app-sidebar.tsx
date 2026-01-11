import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    BookOpen,
    Calendar,
    FileText,
    Folder,
    LayoutGrid,
    Receipt,
    Settings,
    Stethoscope,
    Users,
} from 'lucide-react';
import AppLogo from './app-logo';

const allNavItems: (NavItem & { roles: string[] })[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        roles: ['admin', 'doctor', 'staff', 'patient'],
    },
    {
        title: 'Patients',
        href: '/patients',
        icon: Users,
        roles: ['admin', 'doctor', 'staff'],
    },
    {
        title: 'Doctors',
        href: '/doctors',
        icon: Stethoscope,
        roles: ['admin', 'staff'],
    },
    {
        title: 'Appointments',
        href: '/appointments',
        icon: Calendar,
        roles: ['admin', 'doctor', 'staff', 'patient'],
    },
    {
        title: 'Billing',
        href: '/billing',
        icon: Receipt,
        roles: ['admin', 'doctor', 'staff'],
    },
    {
        title: 'Medical Records',
        href: '/medical-records',
        icon: FileText,
        roles: ['admin', 'doctor'],
    },
    {
        title: 'Reports',
        href: '/reports',
        icon: BarChart3,
        roles: ['admin', 'doctor', 'staff'],
    },
    {
        title: 'Settings',
        href: '/settings/profile',
        icon: Settings,
        roles: ['admin', 'doctor', 'staff', 'patient'],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { auth } = usePage().props as any;
    const user = auth.user;
    const userRole = user.role;

    const mainNavItems = allNavItems.filter((item) =>
        item.roles.includes(userRole),
    );

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
