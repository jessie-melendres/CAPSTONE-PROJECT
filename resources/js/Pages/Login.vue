<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, CalendarDays, Eye, EyeOff, Loader2, Mail, Megaphone, TriangleAlert } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';

defineProps<{ contact: { email: string; office: string; address: string } }>();

const page = usePage<any>();
const logo = computed(() => page.props.logoUrl as string);

const form = useForm({
    access_type: 'student',
    student_id: '',
    student_password: '',
    staff_email: '',
    staff_password: '',
});

const showPassword = ref(false);
const capsLock = ref(false);
const formEl = ref<HTMLFormElement | null>(null);

const trackCapsLock = (event: KeyboardEvent) => {
    capsLock.value = event.getModifierState?.('CapsLock') ?? false;
};

const submit = () =>
    form.post('/login', {
        onFinish: () => form.reset('student_password', 'staff_password'),
        onError: () => nextTick(() => formEl.value?.querySelector<HTMLInputElement>('[aria-invalid="true"]')?.focus()),
    });

const generalError = computed(() => (form.errors as Record<string, string>).login ?? (form.errors as Record<string, string>).access_type);

const perks = [
    { icon: BookOpen, text: 'Grades and general average' },
    { icon: CalendarDays, text: 'Your timetable and enrolled subjects' },
    { icon: Megaphone, text: 'Announcements from the school' },
];

const tabClass = 'h-10 rounded-md text-muted-foreground data-[state=active]:border-b-2 data-[state=active]:border-b-primary data-[state=active]:font-semibold data-[state=active]:text-primary';
const inputClass = 'h-11';
</script>

<template>
    <Head title="Sign in" />
    <main class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden flex-col justify-between overflow-hidden bg-sidebar p-12 text-sidebar-foreground lg:flex">
            <img :src="logo" alt="" class="pointer-events-none absolute right-12 bottom-12 size-72 opacity-15" />
            <Link href="/" class="relative flex items-center gap-4">
                <img :src="logo" alt="NCBII logo" class="size-16 object-contain" />
                <span class="leading-tight">
                    <span class="block text-lg font-semibold text-white">North Coast Bohol Institute</span>
                    <span class="block text-sm text-sidebar-primary">Student Portal</span>
                </span>
            </Link>
            <div class="relative max-w-md space-y-5">
                <p class="text-xs font-semibold tracking-widest text-sidebar-primary">NCBII ACADEMIC INFORMATION SYSTEM</p>
                <h2 class="text-4xl font-bold leading-tight text-white">Your academic journey starts here.</h2>
                <p class="text-sidebar-foreground/80">Sign in with your school credentials to reach:</p>
                <ul class="space-y-3">
                    <li v-for="perk in perks" :key="perk.text" class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-full bg-white/10"><component :is="perk.icon" class="size-4 text-sidebar-primary" aria-hidden="true" /></span>
                        {{ perk.text }}
                    </li>
                </ul>
            </div>
            <p class="relative text-xs text-sidebar-foreground/60">&copy; North Coast Bohol Institute Incorporated</p>
        </div>

        <section class="relative flex items-center justify-center p-6" aria-labelledby="login-title">
            <Link href="/" class="absolute left-6 top-6 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary"><ArrowLeft class="size-4" aria-hidden="true" /> Back to home</Link>
            <Card class="w-full max-w-lg">
                <CardHeader class="px-8">
                    <Link href="/" class="mb-2 flex items-center gap-3 lg:hidden" aria-label="NCBII home">
                        <img :src="logo" alt="NCBII logo" class="size-12 object-contain" />
                        <span class="text-sm font-semibold leading-tight text-primary">North Coast Bohol Institute<span class="block text-xs font-normal text-muted-foreground">Student Portal</span></span>
                    </Link>
                    <p class="text-xs font-semibold tracking-widest text-gold">PORTAL ACCESS</p>
                    <CardTitle id="login-title" class="text-3xl text-primary">Welcome back</CardTitle>
                    <CardDescription>Choose your access type to continue.</CardDescription>
                </CardHeader>
                <CardContent class="px-8">
                    <p v-if="generalError" role="alert" class="mb-4 rounded-md border-l-4 border-destructive bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ generalError }}</p>
                    <form ref="formEl" @submit.prevent="submit" class="space-y-5" novalidate>
                        <Tabs v-model="form.access_type">
                            <TabsList class="grid h-11 w-full grid-cols-2">
                                <TabsTrigger value="student" :class="tabClass">Student</TabsTrigger>
                                <TabsTrigger value="staff" :class="tabClass">Faculty &amp; Staff</TabsTrigger>
                            </TabsList>
                            <TabsContent value="student" class="space-y-4 pt-5">
                                <div class="space-y-2">
                                    <Label for="student_id">Student ID Number</Label>
                                    <Input id="student_id" v-model="form.student_id" placeholder="e.g. 2026-0001" autocomplete="username" autofocus :class="inputClass" :aria-invalid="!!form.errors.student_id" aria-describedby="student_id_hint student_id_error" />
                                    <p id="student_id_hint" class="text-xs text-muted-foreground">Format: 2026-0001</p>
                                    <p v-if="form.errors.student_id" id="student_id_error" class="text-sm text-destructive">{{ form.errors.student_id }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="student_password">Password</Label>
                                    <div class="relative">
                                        <Input id="student_password" v-model="form.student_password" :type="showPassword ? 'text' : 'password'" placeholder="Enter your password" autocomplete="current-password" :class="[inputClass, 'pr-11']" :aria-invalid="!!form.errors.student_password" aria-describedby="student_password_error" @keyup="trackCapsLock" @keydown="trackCapsLock" @blur="capsLock = false" />
                                        <button type="button" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-muted-foreground hover:text-primary" :aria-label="showPassword ? 'Hide password' : 'Show password'" @click="showPassword = !showPassword">
                                            <EyeOff v-if="showPassword" class="size-4" /><Eye v-else class="size-4" />
                                        </button>
                                    </div>
                                    <p v-if="capsLock" class="flex items-center gap-1 text-xs text-gold" role="status"><TriangleAlert class="size-3.5" aria-hidden="true" /> Caps Lock is on</p>
                                    <p v-if="form.errors.student_password" id="student_password_error" class="text-sm text-destructive">{{ form.errors.student_password }}</p>
                                </div>
                            </TabsContent>
                            <TabsContent value="staff" class="space-y-4 pt-5">
                                <div class="space-y-2">
                                    <Label for="staff_email">Username or staff email</Label>
                                    <Input id="staff_email" v-model="form.staff_email" autocomplete="username" :class="inputClass" :aria-invalid="!!form.errors.staff_email" aria-describedby="staff_email_hint staff_email_error" />
                                    <p id="staff_email_hint" class="text-xs text-muted-foreground">Your school username or email</p>
                                    <p v-if="form.errors.staff_email" id="staff_email_error" class="text-sm text-destructive">{{ form.errors.staff_email }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="staff_password">Password</Label>
                                    <div class="relative">
                                        <Input id="staff_password" v-model="form.staff_password" :type="showPassword ? 'text' : 'password'" placeholder="Enter your password" autocomplete="current-password" :class="[inputClass, 'pr-11']" :aria-invalid="!!form.errors.staff_password" aria-describedby="staff_password_error" @keyup="trackCapsLock" @keydown="trackCapsLock" @blur="capsLock = false" />
                                        <button type="button" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-muted-foreground hover:text-primary" :aria-label="showPassword ? 'Hide password' : 'Show password'" @click="showPassword = !showPassword">
                                            <EyeOff v-if="showPassword" class="size-4" /><Eye v-else class="size-4" />
                                        </button>
                                    </div>
                                    <p v-if="capsLock" class="flex items-center gap-1 text-xs text-gold" role="status"><TriangleAlert class="size-3.5" aria-hidden="true" /> Caps Lock is on</p>
                                    <p v-if="form.errors.staff_password" id="staff_password_error" class="text-sm text-destructive">{{ form.errors.staff_password }}</p>
                                </div>
                            </TabsContent>
                        </Tabs>
                        <Button type="submit" size="lg" class="h-11 w-full" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="animate-spin" aria-hidden="true" />
                            {{ form.processing ? 'Signing in…' : 'Continue to Dashboard' }}
                        </Button>
                    </form>
                    <div class="mt-6 space-y-1 border-t pt-5 text-sm text-muted-foreground">
                        <p class="font-medium text-foreground">Forgot your password?</p>
                        <p>Contact the {{ contact.office }}:</p>
                        <a :href="`mailto:${contact.email}`" class="inline-flex items-center gap-2 font-medium text-primary hover:underline"><Mail class="size-4" aria-hidden="true" /> {{ contact.email }}</a>
                    </div>
                </CardContent>
            </Card>
        </section>
    </main>
</template>
