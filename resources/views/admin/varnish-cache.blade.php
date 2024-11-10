@extends('admin::layouts.main')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h2><a href="{{ route('admin.configuration') }}" class="btn mr-4">@include('admin::icons.back') Back</a>Varnish Settings</h2>
        @if(session('message'))
            <notify><span class="notify-success">{{ session('message') }}</span></notify>
        @endif
        @if(session('error'))
            <notify><span class="notify-error">{{ session('error') }}</span></notify>
        @endif

        <form method="post" action="{{ route('admin.modules.samjuk-varnish.purge') }}">
            @method('put')
            {{ csrf_field() }}

            <div class="flex gap-6 card">
                <fieldset class="w-full">
                    <h3>Varnish Purge</h3>
                    
                    <div class="mt-2">
                        <button class="btn btn-secondary" name="purge" value="all" type="submit">Purge Entire Varnish Cache</button>
                    </div>
                    
                    <div class="mt-2">
                        <button class="btn btn-secondary" name="purge" value="uri" type="submit">Purge URI</button>
                        <input type="text" name="uri" placeholder="URI">
                    </div>
                    
                    <div class="mt-2">
                        <button class="btn btn-secondary" name="purge" value="tags" type="submit">Purge Tags</button>
                        <input type="text" name="tags" placeholder="X-Cache-Tags Regex">
                    </div>
                </fieldset>
                
                <fieldset class="w-full flex flex-col">
                    <h3>Varnish Purge Entity</h3>
                    
                    <button class="btn btn-secondary float-right" name="purge" value="entities" type="submit">Purge Entity</button>
                    <select multiple name="entities[]" class="h-full w-full">
                        <optgroup label="Products" selected>
                            @foreach(\Aero\Catalog\Models\Product::all()->pluck('name', 'id') as $k => $v)
                            <option value="P_{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Category">
                            @foreach(\Aero\Catalog\Models\Category::all()->pluck('name', 'id') as $k => $v)
                            <option value="C_{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="CMS Block">
                            @foreach(\Aero\Content\Models\Block::all()->pluck('name', 'id') as $k => $v)
                            <option value="B_{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="CMS Page">
                            @foreach(\Aero\Content\Models\Page::all()->pluck('name', 'id') as $k => $v)
                            <option value="PG_{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </fieldset>
            </div>
        </form>

        <div class="flex gap-6">
            <form method="post" action="{{ route('admin.modules.samjuk-varnish.update') }}">
                @method('put')
                {{ csrf_field() }}
                
                <fieldset class="card mt-4 p-4 w-full">
                    <div class="mb-4">
                        <h3 class="mb-0 pb-0">Varnish Configuration</h3>
                        <p class="opacity-50">Use this to configure how Aero will tell Varnish content has changed.</p>
                    </div>

                    <div class="mt-2">
                        <label for="enabled">Enabled</label>
                        <input type="checkbox" name="enabled" value="1" @if (setting('samjuk-varnish.enabled')) checked="checked" @endif>
                        @error('enabled')<p class="mt-2 mb-4 text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-2">
                        <label for="debug_mode">Enable Debug Mode</label>
                        <select name="debug_mode" id="debug_mode">
                            <option value="0" @if (!setting('samjuk-varnish.debug_mode')) selected @endif>Debug Disabled</option>
                            <option value="1" @if (setting('samjuk-varnish.debug_mode')) selected @endif>Debug Enabled</option>
                        </select>
                        @error('debug_mode')<p class="mt-2 mb-4 text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-2">
                        <label for="host" class="block">Varnish Host</label>
                        <input type="text" name="varnish_host" value="{{ setting('samjuk-varnish.varnish_host')}}">
                        @error('varnish_host')<p class="mt-2 mb-4 text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-2">
                        <label for="host" class="block">Varnish Port</label>
                        <input type="text" name="varnish_port" value="{{ setting('samjuk-varnish.varnish_port')}}">
                        @error('varnish_port')<p class="mt-2 mb-4 text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-2">
                        <label for="host" class="block">Application Host</label>
                        <input type="text" name="app_host" value="{{ setting('samjuk-varnish.app_host')}}">
                        @error('app_host')<p class="mt-2 mb-4 text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-secondary" type="submit">Save</button>
                    </div>
                </fieldset>
            </form>

            <form method="post" action="{{ route('admin.modules.samjuk-varnish.vcl') }}">
                @method('put')
                {{ csrf_field() }}

                <fieldset class="card mt-4 p-4 w-full">
                    <div class="mb-4">
                        <h3 class="mb-0 pb-0">Varnish VCL Generation</h3>
                        <p class="opacity-50">Use this to generate a base VCL for varnish. Make sure to review the configuration before applying</p>
                    </div>

                    <div class="mt-2">
                        <label for="host" class="block">Backend Host</label>
                        <input type="text" name="backend_host" placeholder="localhost">
                    </div>

                    <div class="mt-2">
                        <label for="host" class="block">Backend Port</label>
                        <input type="text" name="backend_port" placeholder="8080">
                    </div>

                    <div class="mt-2">
                        <label for="host" class="block">Purge Allow List</label>
                        <input type="text" name="purge_allow" placeholder="0.0.0.0/0">
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-secondary" type="submit">Generate</button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>

@endsection
