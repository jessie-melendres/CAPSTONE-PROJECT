<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';

const page = usePage<any>();
const logo = computed(() => page.props.logoUrl as string);

const form = useForm({
    access_type: 'student',
    student_id: '',
    student_password: '',
    staff_email: '',
    staff_password: '',
});

const submit = () => form.post('/login', { onFinish: () => form.reset('student_password', 'staff_password') });
const firstError = computed(() => Object.values(form.errors)[0]);
</script>

<template>
    <Head title="Sign in" />
    <main class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden flex-col justify-between overflow-hidden bg-sidebar p-10 text-sidebar-foreground lg:flex">
            <img :src="logo" alt="" class="pointer-events-none absolute -right-24 -bottom-24 size-[28rem] opacity-10" />
            <div class="relative flex items-center gap-3">
                <img :src="logo" alt="NCBII logo" class="size-12 object-contain" />
                <div class="leading-tight">
                    <p class="font-semibold">North Coast Bohol Institute</p>
                    <p class="text-xs text-sidebar-primary">Student Portal</p>
                </div>
            </div>
            <div class="relative max-w-md space-y-3">
                <p class="text-xs font-semibold tracking-widest text-sidebar-primary">NCBII ACADEMIC INFORMATION SYSTEM</p>
                <h2 class="text-4xl font-bold leading-tight text-white">Your academic journey starts here.</h2>
                <p class="text-sidebar-foreground/80">Sign in to access your academic dashboard.</p>
            </div>
        </div>
        <section class="flex items-center justify-center p-6" aria-labelledby="login-title">
            <Card class="w-full max-w-md">
                <CardHeader>
                    <p class="text-xs font-semibold tracking-widest text-gold">PORTAL ACCESS</p>
                    <CardTitle id="login-title" class="text-2xl text-primary">Welcome back</CardTitle>
                    <CardDescription>Choose your access type to continue.</CardDescription>
                </CardHeader>
                <CardContent>
                    <p v-if="firstError" role="alert" class="mb-4 rounded-md border-l-4 border-destructive bg-destructive/10 px-3 py-2 text-sm text-destructive">
                        {{ firstError }}
                    </p>
                    <form @submit.prevent="submit" class="space-y-4">
                        <Tabs v-model="form.access_type">
                            <TabsList class="grid w-full grid-cols-2">
                                <TabsTrigger value="student">Student</TabsTrigger>
                                <TabsTrigger value="staff">Faculty / Administrator</TabsTrigger>
                            </TabsList>
                            <TabsContent value="student" class="space-y-4 pt-4">
                                <div class="space-y-2">
                                    <Label for="student_id">Student ID Number</Label>
                                    <Input id="student_id" v-model="form.student_id" placeholder="e.g. 2026-0001" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="student_password">Password</Label>
                                    <Input id="student_password" v-model="form.student_password" type="password" />
                                </div>
                            </TabsContent>
                            <TabsContent value="staff" class="space-y-4 pt-4">
                                <div class="space-y-2">
                                    <Label for="staff_email">Username or staff email</Label>
                                    <Input id="staff_email" v-model="form.staff_email" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="staff_password">Password</Label>
                                    <Input id="staff_password" v-model="form.staff_password" type="password" />
                                </div>
                            </TabsContent>
                        </Tabs>
                        <Button type="submit" class="w-full" :disabled="form.processing">Continue to Dashboard</Button>
                    </form>
                </CardContent>
            </Card>
        </section>
    </main>
</template>
