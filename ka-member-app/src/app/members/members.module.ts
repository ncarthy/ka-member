import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { NgxBootstrapIconsModule } from 'ngx-bootstrap-icons'; //https://avmaisak.github.io/ngx-bootstrap-icons/home
import { calendarCheck, calendarDate } from 'ngx-bootstrap-icons';

import { MembersRoutingModule } from './members-routing.module';
import { LayoutComponent } from './layout.component';
import { ListComponent } from './list.component';
import { AddEditComponent } from './add-edit.component';
import { RowComponent } from './row.component';

//import { MemberSearchComponent } from '@app/member-search/member-search.component';

const icons = {
  calendarCheck,
  calendarDate
};

@NgModule({
  imports: [
      CommonModule,
      ReactiveFormsModule,
      MembersRoutingModule,
      NgbModule,
      NgxBootstrapIconsModule.pick(icons)
  ],
  declarations: [
      LayoutComponent,
      ListComponent,
      AddEditComponent,
      RowComponent
  ]
})
export class MembersModule { }
