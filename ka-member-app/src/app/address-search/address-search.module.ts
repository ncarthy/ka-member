import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AddressSearchService, ADDRESS_API_KEY, ADDRESS_API_URL } from '@app/_services';
import { AddressSearchComponent } from './address-search.component';
import { SearchBoxComponent } from './search-box.component';
//import { SearchResultComponent } from './search-result.component'

@NgModule({
  declarations: [
    AddressSearchComponent, 
    SearchBoxComponent,
    //SearchResultComponent
  ],
  exports: [
    AddressSearchComponent, 
    SearchBoxComponent,
    //SearchResultComponent
  ],
  imports: [
    CommonModule,
    FormsModule
  ],
  providers: [AddressSearchService,
    {provide: ADDRESS_API_KEY, useValue: ADDRESS_API_KEY},
    {provide: ADDRESS_API_URL, useValue: ADDRESS_API_URL}]
})
export class AddressSearchModule { }
