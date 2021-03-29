import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import {
  AddressSearchService,
  ADDRESS_API_KEY,
  ADDRESS_API_URL,
} from '@app/_services';

import { AlertComponent } from './alert-component/alert.component';
import { AddressFormComponent } from './address-form/address-form.component';
import { SearchBoxComponent } from './address-form/search-box.component';
import { ToastContainerComponent } from './toast-container/toast-container.component';

@NgModule({
  imports: [CommonModule, FormsModule, ReactiveFormsModule],
  declarations: [
    AlertComponent,
    AddressFormComponent,
    SearchBoxComponent,
    ToastContainerComponent,
  ],
  exports: [
    AlertComponent,
    AddressFormComponent,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    SearchBoxComponent,
    ToastContainerComponent,
  ],
  providers: [
    AddressSearchService,
    { provide: ADDRESS_API_KEY, useValue: ADDRESS_API_KEY },
    { provide: ADDRESS_API_URL, useValue: ADDRESS_API_URL },
  ],
})
export class SharedModule {}
