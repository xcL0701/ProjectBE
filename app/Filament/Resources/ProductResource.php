<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\Rules\File;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Barang')
                    ->required(),
                Select::make('machine_id')
                    ->relationship('machine', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                FileUpload::make('thumbnail')
                    ->image()
                    ->label('Foto Barang Untuk Thumbnail')
                    ->required(),
                Textarea::make('desc')
                    ->label('Deskripsi Barang')
                    ->required()
                    ->rows(10)
                    ->cols(20),
                Repeater::make('productPhotos')->relationship('productPhotos')->label('Foto Barang')->schema([
                    FileUpload::make('photo')
                        ->image()
                        ->label('Foto Barang')
                        ->helperText('Boleh sama seperti Thumbnail')
                        ->required(),
                    ])
                    ->minItems(1),
                FileUpload::make('model_3d')
                    ->label('Model 3D')
                    ->helperText('Unggah file 3D berformat `.glb` maksimal 10MB. 1 File 3D saja')
                    ->acceptedFileTypes([
                        'model/gltf-binary',
                        'application/octet-stream',
                        '.glb',
                        '*.glb',
                    ])
                    ->rules([
                        'mimetypes:model/gltf-binary, application/octet-stream',
                        'mimes:glb',
                        'max:10240', // max dalam KB = 10MB
                    ])
                    ->disk('public')
                    ->visibility('public')
                    ->directory('models')
                    ->label('Model 3D (Opsional)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama Barang')->searchable()->sortable(),
                TextColumn::make('machine.name')->label('Tipe Mesin')->searchable()->sortable(),
                Tables\Columns\ImageColumn::make('thumbnail')->label('Thumbnail'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
