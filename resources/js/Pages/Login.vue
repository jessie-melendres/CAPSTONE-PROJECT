<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Eye, EyeOff, Loader2 } from '@lucide/vue';
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
const formEl = ref<HTMLFormElement | null>(null);

const submit = () =>
    form.post('/login', {
        onFinish: () => form.reset('student_password', 'staff_password'),
        onError: () => nextTick(() => formEl.value?.querySelector<HTMLInputElement>('[aria-invalid="true"]')?.focus()),
    });

const generalError = computed(() => (form.errors as Record<string, string>).login ?? (form.errors as Record<string, string>).access_type);
</script>

<template>
    <Head title="Sign in" />
    <main class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden flex-col justify-between overflow-hidden bg-sidebar p-10 text-sidebar-foreground lg:flex">
            <img :src="logo" alt="" class="pointer-events-none absolute -right-24 -bottom-24 size-[28rem] opacity-10" />
            <Link href="/" class="relative flex items-center gap-3">
                <img :src="logo" alt="NCBII logo" class="size-12 object-contain" />
                <span class="leading-tight">
                    <span class="block font-semibold">North Coast Bohol Institute</span>
                    <span class="block text-xs text-sidebar-primary">Student Portal</span>
                </span>
            </Link>
            <div class="relative max-w-md space-y-3">
                <p class="text-xs font-semibold tracking-widest text-sidebar-primary">NCBII ACADEMIC INFORMATION SYSTEM</p>
                <h2 class="text-4xl font-bold leading-tight text-white">Your academic journey starts here.</h2>
                <p class="text-sidebar-foreground/80">Sign in with your school credentials to reach your grades, schedule and announcements.</p>
            </div>
        </div>

        <section class="flex flex-col items-center justify-center gap-6 p-6" aria-labelledby="login-title">
            <div class="w-full max-w-md">
                <Link href="/" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary"><ArrowLeft class="size-4" aria-hidden="true" /> Back to home</Link>
            </div>
            <Card class="w-full max-w-md">
                <CardHeader>
                    <p class="text-xs font-semibold tracking-widest text-gold">PORTAL ACCESS</p>
                    <CardTitle id="login-title" class="text-2xl text-primary">Welcome back</CardTitle>
                    <CardDescription>Choose your access type to continue.</CardDescription>
                </CardHeader>
                <CardContent>
                    <p v-if="generalError" role="alert" class="mb-4 rounded-md border-l-4 border-destructive bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ generalError }}</p>
                    <form ref="formEl" @submit.prevent="submit" class="space-y-4" novalidate>
                        <Tabs v-model="form.access_type">
                            <TabsList class="grid w-full grid-cols-2">
                                <TabsTrigger value="student">Student</TabsTrigger>
                                <TabsTrigger value="staff">Faculty &amp; Staff</TabsTrigger>
                            </TabsList>
                            <TabsContent value="student" class="space-y-4 pt-4">
                                <div class="space-y-2">
                                    <Label for="student_id">Student ID Number</Label>
                                    <Input id="student_id" v-model="form.student_id" placeholder="e.g. 2026-0001" autocomplete="username" autofocus :aria-invalid="!!form.errors.student_id" aria-describedby="student_id_error" />
                                    <p v-if="form.errors.student_id" id="student_id_error" class="text-sm text-destructive">{{ form.errors.student_id }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="student_password">Password</Label>
                                    <div class="relative">
                                        <Input id="student_password" v-model="form.student_password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" class="pr-10" :aria-invalid="!!form.errors.student_password" aria-describedby="student_password_error" />
                                        <button type="button" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-muted-foreground hover:text-primary" :aria-label="showPassword ? 'Hide password' : 'Show password'" @click="showPassword = !showPassword">
                                            <EyeOff v-if="showPassword" class="size-4" /><Eye v-else class="size-4" />
                                        </button>
                                    </div>
                                    <p v-if="form.errors.student_password" id="student_password_error" class="text-sm text-destructive">{{ form.errors.student_password }}</p>
                                </div>
                            </TabsContent>
                            <TabsContent value="staff" class="space-y-4 pt-4">
                                <div class="space-y-2">
                                    <Label for="staff_email">Username or staff email</Label>
                                    <Input id="staff_email" v-model="form.staff_email" autocomplete="username" :aria-invalid="!!form.errors.staff_email" aria-describedby="staff_email_error" />
                                    <p v-if="form.errors.staff_email" id="staff_email_error" class="text-sm text-destructive">{{ form.errors.staff_email }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="staff_password">Password</Label>
                                    <div class="relative">
                                        <Input id="staff_password" v-model="form.staff_password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" class="pr-10" :aria-invalid="!!form.errors.staff_password" aria-describedby="staff_password_error" />
                                        <button type="button" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-muted-foreground hover:text-primary" :aria-label="showPassword ? 'Hide password' : 'Show password'" @click="showPassword = !showPassword">
                                            <EyeOff v-if="showPassword" class="size-4" /><Eye v-else class="size-4" />
                                        </button>
                                    </div>
                                    <p v-if="form.errors.staff_password" id="staff_password_error" class="text-sm text-destructive">{{ form.errors.staff_password }}</p>
                                </div>
                            </TabsContent>
                        </Tabs>
                        <Button type="submit" class="w-full" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="animate-spin" aria-hidden="true" />
                            {{ form.processing ? 'Signing in…' : 'Continue to Dashboard' }}
                        </Button>
                    </form>
                    <p class="mt-5 text-center text-sm text-muted-foreground">
                        Forgot your password or can't sign in? Contact the {{ contact.office }} at
                        <a :href="`mailto:${contact.email}`" class="font-medium text-primary hover:underline">{{ contact.email }}</a>.
                    </p>
                </CardContent>
            </Card>
        </section>
    </main>
</template>
