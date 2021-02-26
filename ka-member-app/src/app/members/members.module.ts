import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  RouterModule,
  ActivatedRoute,
  Router,
  Routes
} from '@angular/router';

import { MembersComponent } from './members.component';
import { MemberComponent } from './member/member.component';


@NgModule({
  declarations: [
    MembersComponent,
    MemberComponent,
  ],
  exports: [
    MembersComponent,
    MemberComponent,
  ],
  imports: [
    CommonModule,
    RouterModule
  ]
})
export class MembersModule { }
