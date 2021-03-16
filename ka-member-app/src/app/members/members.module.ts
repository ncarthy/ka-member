import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { MembersRoutingModule } from './members-routing.module';
import { LayoutComponent } from './layout.component';
import { ListComponent } from './list.component';
import { AddEditComponent } from './add-edit.component';
import { RowComponent } from './row.component';


@NgModule({
  imports: [
      CommonModule,
      ReactiveFormsModule,
      MembersRoutingModule,
      NgbModule
  ],
  declarations: [
      LayoutComponent,
      ListComponent,
      AddEditComponent,
      RowComponent
  ]
})
export class MembersModule { }
