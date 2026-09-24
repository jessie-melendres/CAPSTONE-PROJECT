<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { BookOpen, CalendarDays, GraduationCap, LayoutDashboard, Megaphone, Menu, Users, Mail, MapPin } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Sheet, SheetClose, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';

defineProps<{
    announcements: { id: number; title: string | null; content: string; date_posted: string }[];
    contact: { email: string; office: string; address: string };
    term: string;
}>();

const page = usePage<any>();
const logo = computed(() => page.props.logoUrl as string);

const navLinks = [
    { label: 'Home', href: '/' },
    { label: 'About', href: '/about' },
    { label: 'Features', href: '/features' },
];

const roles = [
    { icon: GraduationCap, title: 'Students', text: 'View your grades, general average, timetable and tuition balance.' },
    { icon: Users, title: 'Faculty', text: 'Encode grades for your classes, lock them when final, and post announcements.' },
    { icon: LayoutDashboard, title: 'Administrators', text: 'Manage students, faculty, subjects, schedules and enrollment for the institute.' },
];
</script>

<template>
    <Head title="Welcome" />
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-primary focus:px-3 focus:py-2 focus:text-primary-foreground">Skip to content</a>

        <header class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
                <Link href="/" class="flex items-center gap-3">
                    <img :src="logo" alt="NCBII logo" class="size-10 object-contain" />
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold text-primary">North Coast Bohol Institute</span>
                        <span class="block text-xs text-muted-foreground">Student Portal</span>
                    </span>
                </Link>
                <nav class="hidden items-center gap-6 text-sm font-medium md:flex" aria-label="Main">
                    <a v-for="link in navLinks" :key="link.href" :href="link.href" class="text-foreground/80 hover:text-primary" :aria-current="link.href === '/' ? 'page' : undefined">{{ link.label }}</a>
                    <Button as-child><Link href="/login">Sign in</Link></Button>
                </nav>
                <Sheet>
                    <SheetTrigger as-child>
                        <Button variant="ghost" size="icon" class="md:hidden" aria-label="Open menu"><Menu /></Button>
                    </SheetTrigger>
                    <SheetContent side="right" class="w-64">
                        <SheetHeader><SheetTitle>Menu</SheetTitle></SheetHeader>
                        <nav class="flex flex-col gap-1 px-4" aria-label="Mobile">
                            <SheetClose v-for="link in navLinks" :key="link.href" as-child>
                                <a :href="link.href" class="rounded-md px-3 py-2 text-sm font-medium hover:bg-accent">{{ link.label }}</a>
                            </SheetClose>
                            <Button as-child class="mt-3"><Link href="/login">Sign in</Link></Button>
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>
        </header>

        <main id="main" class="flex-1">
            <section class="relative overflow-hidden bg-sidebar text-sidebar-foreground">
                <img :src="logo" alt="" class="pointer-events-none absolute -right-20 -bottom-24 size-[26rem] opacity-10" />
                <div class="relative mx-auto grid max-w-6xl gap-10 px-4 py-16 md:py-24 lg:grid-cols-[1.2fr_1fr] lg:items-center">
                    <div class="space-y-6">
                        <p class="text-xs font-semibold tracking-widest text-sidebar-primary">WELCOME TO NCBII</p>
                        <h1 class="text-4xl font-bold leading-tight text-white md:text-5xl">Your academic journey, <span class="text-sidebar-primary">connected.</span></h1>
                        <p class="max-w-xl text-lg text-sidebar-foreground/85">Grades, schedules, enrollment and school announcements in one secure portal for students, faculty and administrators.</p>
                        <div class="flex flex-wrap gap-3">
                            <Button as-child size="lg" class="bg-gold text-white hover:bg-gold/90"><Link href="/login">Sign in to the portal</Link></Button>
                            <Button as-child size="lg" variant="outline" class="border-sidebar-foreground/40 bg-transparent text-sidebar-foreground hover:bg-sidebar-accent hover:text-white"><a href="/features">Explore features</a></Button>
                        </div>
                    </div>
                    <div class="rounded-xl border border-sidebar-border bg-white/5 p-6 backdrop-blur">
                        <p class="text-xs font-semibold tracking-widest text-sidebar-primary">CURRENT TERM</p>
                        <p class="mt-1 text-2xl font-semibold text-white">{{ term }}</p>
                        <ul class="mt-5 space-y-3 text-sm">
                            <li class="flex items-center gap-3"><BookOpen class="size-4 text-sidebar-primary" /> Grades and general average</li>
                            <li class="flex items-center gap-3"><CalendarDays class="size-4 text-sidebar-primary" /> Personal timetable</li>
                            <li class="flex items-center gap-3"><Megaphone class="size-4 text-sidebar-primary" /> Announcements from the school</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-6xl px-4 py-14" aria-labelledby="roles-title">
                <p class="text-xs font-semibold tracking-widest text-gold">BUILT FOR EVERYONE ON CAMPUS</p>
                <h2 id="roles-title" class="mt-1 text-2xl font-bold text-primary">One portal, three workspaces</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <Card v-for="role in roles" :key="role.title">
                        <CardHeader>
                            <component :is="role.icon" class="mb-2 size-8 text-gold" aria-hidden="true" />
                            <CardTitle class="text-primary">{{ role.title }}</CardTitle>
                            <CardDescription>{{ role.text }}</CardDescription>
                        </CardHeader>
                    </Card>
                </div>
            </section>

            <section v-if="announcements.length" class="bg-secondary/50 py-14" aria-labelledby="news-title">
                <div class="mx-auto max-w-6xl px-4">
                    <p class="text-xs font-semibold tracking-widest text-gold">LATEST</p>
                    <h2 id="news-title" class="mt-1 text-2xl font-bold text-primary">Announcements</h2>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <Card v-for="item in announcements" :key="item.id">
                            <CardHeader>
                                <p class="text-xs text-muted-foreground">{{ item.date_posted }}</p>
                                <CardTitle class="text-base text-primary">{{ item.title || 'Announcement' }}</CardTitle>
                            </CardHeader>
                            <CardContent class="line-clamp-4 text-sm text-muted-foreground">{{ item.content }}</CardContent>
                        </Card>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t bg-sidebar text-sidebar-foreground">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="font-semibold text-white">North Coast Bohol Institute Incorporated</p>
                    <p class="mt-1 flex items-center gap-2 text-sidebar-foreground/80"><MapPin class="size-4" aria-hidden="true" /> {{ contact.address }}</p>
                </div>
                <div>
                    <p class="text-sidebar-foreground/80">{{ contact.office }}</p>
                    <a :href="`mailto:${contact.email}`" class="mt-1 flex items-center gap-2 text-sidebar-primary hover:underline"><Mail class="size-4" aria-hidden="true" /> {{ contact.email }}</a>
                </div>
            </div>
        </footer>
    </div>
</template>
