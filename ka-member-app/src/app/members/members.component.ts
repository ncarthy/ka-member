import { Component, OnInit } from '@angular/core';
import {
  RouterModule,
  ActivatedRoute,
  Router
} from '@angular/router';

@Component({
  selector: 'app-members',
  templateUrl: './members.component.html',
  styleUrls: ['./members.component.css']
})
export class MembersComponent implements OnInit {

  constructor(private router: Router, private route: ActivatedRoute) {
  }

  goToMember(id: string): void {
    this.router.navigate(['./id', id], {relativeTo: this.route});
  }

  ngOnInit(): void {
  }

}
