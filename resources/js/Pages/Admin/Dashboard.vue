<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, BookOpen, CalendarClock, Users, ClipboardCheck, Wallet, ArrowRight } from 'lucide-vue-next';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, CardContent, CardHeader, CardDescription } from '@/components/ui/card';

const props = defineProps<{
    studentCount: number; subjectCount: number; scheduleCount: number; facultyCount: number; enrollmentCount: number;
}>();

const stats = [
    { label: 'Total students', value: props.studentCount, hint: 'Manage student records', view: 'students', icon: GraduationCap },
    { label: 'Subjects', value: props.subjectCount, hint: 'Manage academic offerings', view: 'subjects', icon: BookOpen },
    { label: 'Class schedules', value: props.scheduleCount, hint: 'Room / faculty / time slots', view: 'schedules', icon: CalendarClock },
    { label: 'Faculty', value: props.facultyCount, hint: 'Manage faculty accounts', view: 'faculty', icon: Users },
    { label: 'Active enrollments', value: props.enrollmentCount, hint: 'Assign classes to students', view: 'loads', icon: ClipboardCheck },
];
</script>

<template>
    <Head title="Institute overview" />
    <PortalLayout title="Dashboard" portal="Administrator Portal">
        <div class="mx-auto max-w-6xl space-y-6">
            <div>
                <p class="text-xs font-semibold tracking-widest text-gold">ADMINISTRATOR WORKSPACE</p>
                <h2 class="mt-1 text-2xl font-bold text-primary">Institute overview</h2>
                <p class="text-sm text-muted-foreground">Monitor students, subjects, instructors, programs, and academic grades.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link v-for="s in stats" :key="s.view" :href="`/administrator/management?view=${s.view}`">
                    <Card class="group h-full transition hover:border-gold hover:shadow-md">
                        <CardHeader class="flex items-start justify-between">
                            <CardDescription>{{ s.label }}</CardDescription>
                            <span class="rounded-md bg-accent p-2 text-primary"><component :is="s.icon" class="size-4" /></span>
                        </CardHeader>
                        <CardContent>
                            <p class="text-3xl font-bold text-primary">{{ s.value }}</p>
                            <p class="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                {{ s.hint }}<ArrowRight class="size-3 opacity-0 transition group-hover:opacity-100" />
                            </p>
                        </CardContent>
                    </Card>
                </Link>
                <Link href="/administrator/management?view=ledger">
                    <Card class="group h-full border-primary bg-primary text-primary-foreground transition hover:shadow-md">
                        <CardHeader class="flex items-start justify-between">
                            <CardDescription class="text-primary-foreground/80">Portal ledger</CardDescription>
                            <span class="rounded-md bg-white/15 p-2"><Wallet class="size-4" /></span>
                        </CardHeader>
                        <CardContent>
                            <p class="text-3xl font-bold">Open</p>
                            <p class="mt-1 text-xs text-primary-foreground/80">Record charges &amp; payments</p>
                        </CardContent>
                    </Card>
                </Link>
            </div>
        </div>
    </PortalLayout>
</template>
