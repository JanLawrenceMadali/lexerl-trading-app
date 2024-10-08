<script setup>
import { MoreHorizontal } from 'lucide-vue-next';
</script>

<template>
    <TabsContent value="sales">
        <Card>
            <CardHeader>
                <CardTitle>Sales</CardTitle>
                <CardDescription>
                    Manage your products and view their sales performance.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="hidden w-[100px] sm:table-cell">
                                <span class="sr-only">img</span>
                            </TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="hidden md:table-cell">
                                Price
                            </TableHead>
                            <TableHead class="hidden md:table-cell">
                                Total Sales
                            </TableHead>
                            <TableHead class="hidden md:table-cell">
                                Created at
                            </TableHead>
                            <TableHead>
                                <span class="sr-only">Actions</span>
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow>
                            <TableCell class="hidden sm:table-cell">
                                <img alt="Product image" class="object-cover rounded-md aspect-square" height="64"
                                    src="https://www.shadcn-vue.com/placeholder.svg" width="64">
                            </TableCell>
                            <TableCell class="font-medium">
                                Laser Lemonade Machine
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">
                                    Draft
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                $499.99
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                25
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                2023-07-12 10:42 AM
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button aria-haspopup="true" size="icon" variant="ghost">
                                            <MoreHorizontal class="w-4 h-4" />
                                            <span class="sr-only">Toggle menu</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                        <DropdownMenuItem>Edit</DropdownMenuItem>
                                        <DropdownMenuItem>Delete</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="hidden sm:table-cell">
                                <img alt="Product image" class="object-cover rounded-md aspect-square" height="64"
                                    src="https://www.shadcn-vue.com/placeholder.svg" width="64">
                            </TableCell>
                            <TableCell class="font-medium">
                                Hypernova Headphones
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">
                                    Active
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                $129.99
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                100
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                2023-10-18 03:21 PM
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button aria-haspopup="true" size="icon" variant="ghost">
                                            <MoreHorizontal class="w-4 h-4" />
                                            <span class="sr-only">Toggle menu</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                        <DropdownMenuItem>Edit</DropdownMenuItem>
                                        <DropdownMenuItem>Delete</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="hidden sm:table-cell">
                                <img alt="Product image" class="object-cover rounded-md aspect-square" height="64"
                                    src="https://www.shadcn-vue.com/placeholder.svg" width="64">
                            </TableCell>
                            <TableCell class="font-medium">
                                AeroGlow Desk Lamp
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">
                                    Active
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                $39.99
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                50
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                2023-11-29 08:15 AM
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button aria-haspopup="true" size="icon" variant="ghost">
                                            <MoreHorizontal class="w-4 h-4" />
                                            <span class="sr-only">Toggle menu</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                        <DropdownMenuItem>Edit</DropdownMenuItem>
                                        <DropdownMenuItem>Delete</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="hidden sm:table-cell">
                                <img alt="Product image" class="object-cover rounded-md aspect-square" height="64"
                                    src="https://www.shadcn-vue.com/placeholder.svg" width="64">
                            </TableCell>
                            <TableCell class="font-medium">
                                TechTonic Energy Drink
                            </TableCell>
                            <TableCell>
                                <Badge variant="secondary">
                                    Draft
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                $2.99
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                0
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                2023-12-25 11:59 PM
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button aria-haspopup="true" size="icon" variant="ghost">
                                            <MoreHorizontal class="w-4 h-4" />
                                            <span class="sr-only">Toggle menu</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                        <DropdownMenuItem>Edit</DropdownMenuItem>
                                        <DropdownMenuItem>Delete</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="hidden sm:table-cell">
                                <img alt="Product image" class="object-cover rounded-md aspect-square" height="64"
                                    src="https://www.shadcn-vue.com/placeholder.svg" width="64">
                            </TableCell>
                            <TableCell class="font-medium">
                                Gamer Gear Pro Controller
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">
                                    Active
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                $59.99
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                75
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                2024-01-01 12:00 AM
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button aria-haspopup="true" size="icon" variant="ghost">
                                            <MoreHorizontal class="w-4 h-4" />
                                            <span class="sr-only">Toggle menu</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                        <DropdownMenuItem>Edit</DropdownMenuItem>
                                        <DropdownMenuItem>Delete</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="hidden sm:table-cell">
                                <img alt="Product image" class="object-cover rounded-md aspect-square" height="64"
                                    src="https://www.shadcn-vue.com/placeholder.svg" width="64">
                            </TableCell>
                            <TableCell class="font-medium">
                                Luminous VR Headset
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">
                                    Active
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                $199.99
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                30
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                2024-02-14 02:14 PM
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button aria-haspopup="true" size="icon" variant="ghost">
                                            <MoreHorizontal class="w-4 h-4" />
                                            <span class="sr-only">Toggle menu</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                        <DropdownMenuItem>Edit</DropdownMenuItem>
                                        <DropdownMenuItem>Delete</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
            <CardFooter>
                <div class="text-xs text-muted-foreground">
                    Showing <strong>1-10</strong> of <strong>32</strong>
                    products
                </div>
            </CardFooter>
        </Card>
    </TabsContent>
</template>