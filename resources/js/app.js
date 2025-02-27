import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Button } from './Components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from './Components/ui/tabs';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from './Components/ui/dropdown-menu';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from './Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './Components/ui/table';
import { Badge } from './Components/ui/badge';
import { RadioGroup, RadioGroupItem } from './Components/ui/radio-group';
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from './Components/ui/breadcrumb';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

createInertiaApp({
    title: (title) => `${title} - Lexerl Trading App`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(VueSweetalert2)
            .component("Badge", Badge)
            .component("Button", Button)
            .component("Breadcrumb", Breadcrumb)
            .component("BreadcrumbList", BreadcrumbList)
            .component("BreadcrumbItem", BreadcrumbItem)
            .component("BreadcrumbLink", BreadcrumbLink)
            .component("BreadcrumbSeparator", BreadcrumbSeparator)
            .component("BreadcrumbPage", BreadcrumbPage)
            .component("Card", Card)
            .component("CardHeader", CardHeader)
            .component("CardTitle", CardTitle)
            .component("CardDescription", CardDescription)
            .component("CardContent", CardContent)
            .component("CardFooter", CardFooter)
            .component("DropdownMenu", DropdownMenu)
            .component("DropdownMenuTrigger", DropdownMenuTrigger)
            .component("DropdownMenuContent", DropdownMenuContent)
            .component("DropdownMenuLabel", DropdownMenuLabel)
            .component("DropdownMenuSeparator", DropdownMenuSeparator)
            .component("DropdownMenuItem", DropdownMenuItem)
            .component("Link", Link)
            .component("Head", Head)
            .component("RadioGroup", RadioGroup)
            .component("RadioGroupItem", RadioGroupItem)
            .component("Table", Table)
            .component("TableHeader", TableHeader)
            .component("TableRow", TableRow)
            .component("TableHead", TableHead)
            .component("TableBody", TableBody)
            .component("TableCell", TableCell)
            .component("Tabs", Tabs)
            .component("TabsList", TabsList)
            .component("TabsTrigger", TabsTrigger)
            .component("TabsContent", TabsContent)
            .mount(el);
    },
    progress: {
        color: '#b5332e',
        showSpinner: true
    },
});
