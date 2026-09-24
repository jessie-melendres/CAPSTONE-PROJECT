<script setup lang="ts">
import { computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { LayoutDashboard, Database, LogOut } from 'lucide-vue-next';
import { Toaster } from '@/components/ui/sonner';
import {
    Sidebar, SidebarContent, SidebarFooter, SidebarGroup, SidebarGroupContent, SidebarGroupLabel,
    SidebarHeader, SidebarInset, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarProvider,
    SidebarTrigger, SidebarRail,
} from '@/components/ui/sidebar';
import { Separator } from '@/components/ui/separator';

defineProps<{ title: string; portal: string }>();

const page = usePage<any>();
const user = computed(() => page.props.auth.user);
const logo = computed(() => page.props.logoUrl as string);

const navs: Record<string, { label: string; href: string; icon: any }[]> = {
    admin: [
        { label: 'Dashboard', href: '/administrator', icon: LayoutDashboard },
        { label: 'Management', href: '/administrator/management', icon: Database },
    ],
};
const items = computed(() => navs[user.value?.role] ?? []);
const isActive = (href: string) =>
    href === '/administrator' ? page.url === href : page.url.startsWith(href);

watch(() => page.props.flash?.status, (msg) => msg && toast.success(msg as string), { immediate: true });

const logout = () => router.post('/logout');
</script>

<template>
    <SidebarProvider>
        <Sidebar collapsible="icon">
            <SidebarHeader>
                <div class="flex items-center gap-3 px-2 py-2">
                    <img :src="logo" alt="NCBII logo" class="size-9 shrink-0 object-contain" />
                    <div class="min-w-0 leading-tight group-data-[collapsible=icon]:hidden">
                        <p class="truncate text-sm font-semibold text-sidebar-foreground">North Coast Bohol Institute</p>
                        <p class="text-xs text-sidebar-primary">{{ portal }}</p>
                    </div>
                </div>
            </SidebarHeader>
            <SidebarContent>
                <SidebarGroup>
                    <SidebarGroupLabel>Navigation</SidebarGroupLabel>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            <SidebarMenuItem v-for="item in items" :key="item.href">
                                <SidebarMenuButton as-child :is-active="isActive(item.href)" :tooltip="item.label">
                                    <Link :href="item.href"><component :is="item.icon" /><span>{{ item.label }}</span></Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </SidebarContent>
            <SidebarFooter>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton tooltip="Sign out" @click="logout"><LogOut /><span>Sign out</span></SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
            <SidebarRail />
        </Sidebar>
        <SidebarInset>
            <header class="flex h-14 items-center gap-2 border-b bg-background px-4">
                <SidebarTrigger />
                <Separator orientation="vertical" class="mr-2 h-4" />
                <h1 class="text-sm font-medium text-muted-foreground">{{ title }}</h1>
                <span class="ml-auto text-xs text-muted-foreground">{{ user?.username }}</span>
            </header>
            <main class="flex-1 p-6"><slot /></main>
        </SidebarInset>
        <Toaster position="top-right" />
    </SidebarProvider>
</template>
