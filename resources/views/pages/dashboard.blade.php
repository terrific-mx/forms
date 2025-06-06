<?php

use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Volt\Component;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class]);

new class extends Component {

}; ?>

<x-layouts.app :title="__('Forms')">
    @volt('pages.index')
        <div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    <flux:table.row>
                        <flux:table.cell>Contact Form</flux:table.cell>
                    </flux:table.row>
                    <flux:table.row>
                        <flux:table.cell>Feedback Form</flux:table.cell>
                    </flux:table.row>
                    <flux:table.row>
                        <flux:table.cell>Registration Form</flux:table.cell>
                    </flux:table.row>
                </flux:table.rows>
            </flux:table>
        </div>
    @endvolt
</x-layouts.app>
