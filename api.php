<?php require 'config.php';$a=$_GET['a']??'';$in=json_decode(file_get_contents('php://input'),true)??[];$db=db();
if($a==='login'){csrf_login:$s=$db->prepare('SELECT * FROM admins WHERE username=? AND locked=0');$s->execute([$in['u']??'']);$u=$s->fetch();
 if(!$u||!password_verify($in['p']??'',$u['password_hash'])){usleep(500000);out(['error'=>'Sai tài khoản hoặc mật khẩu'],401);}
 session_regenerate_id(true);$_SESSION['u']=['id'=>$u['id'],'username'=>$u['username'],'role'=>$u['role']];$_SESSION['csrf']=bin2hex(random_bytes(16));
 $db->prepare('UPDATE admins SET last_login=NOW() WHERE id=?')->execute([$u['id']]);logit('Đăng nhập');out(['csrf'=>$_SESSION['csrf'],'user'=>$_SESSION['u']]);}
if($a==='me')out(['user'=>$_SESSION['u']??null,'csrf'=>$_SESSION['csrf']??null]);
need();csrf();
if($a==='logout'){logit('Đăng xuất');session_destroy();out(['ok'=>1]);}
$sum=fn($t,$f,$w)=>(int)$db->query("SELECT COALESCE(SUM($f),0) FROM transactions WHERE type='$t' AND status='done' AND $w")->fetchColumn();
if($a==='stats'){$r=[];foreach(['today'=>'DATE(created_at)=CURDATE()','week'=>'YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)','month'=>'YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())','year'=>'YEAR(created_at)=YEAR(CURDATE())'] as $k=>$w)
 $r[$k]=['deposit'=>$sum('deposit','amount',$w),'withdraw'=>$sum('withdraw','amount',$w),'fee'=>(int)$db->query("SELECT COALESCE(SUM(fee),0) FROM transactions WHERE status='done' AND $w")->fetchColumn()];
 $r['players']=(int)$db->query('SELECT COUNT(*) FROM players')->fetchColumn();$r['active']=(int)$db->query("SELECT COUNT(*) FROM players WHERE status='active'")->fetchColumn();
 $r['pending']=(int)$db->query("SELECT COUNT(*) FROM transactions WHERE status='pending'")->fetchColumn();$r['goal']=(int)$db->query("SELECT v FROM settings WHERE k='daily_goal'")->fetchColumn();
 $r['chart']=$db->query("SELECT DATE(created_at) d,SUM(CASE WHEN type='deposit' THEN amount END) dep,SUM(CASE WHEN type='withdraw' THEN amount END) wd FROM transactions WHERE status='done' AND created_at>=CURDATE()-INTERVAL 13 DAY GROUP BY d ORDER BY d")->fetchAll();out($r);}
if($a==='list'){$t=$in['t']??'';$q='%'.($in['q']??'').'%';$pg=max(1,(int)($in['page']??1));$off=($pg-1)*15;
 $map=['players'=>"SELECT id,name,account,balance,status,is_demo,created_at FROM players WHERE name LIKE ? OR account LIKE ?",'tables'=>"SELECT * FROM tables_game WHERE name LIKE ? OR code LIKE ?",
 'logs'=>"SELECT * FROM logs WHERE admin LIKE ? OR action LIKE ?",'admins'=>"SELECT id,username,role,locked,last_login FROM admins WHERE username LIKE ? OR role LIKE ?"];
 if(in_array($t,['deposit','withdraw','transfer','all'])){$w=$t==='all'?'1':"t.type='$t'";$s=$db->prepare("SELECT t.*,p.name pname FROM transactions t LEFT JOIN players p ON p.id=t.player_id WHERE $w AND (p.name LIKE ? OR t.id LIKE ?) ORDER BY t.id DESC LIMIT 15 OFFSET $off");}
 elseif(isset($map[$t])){if($t==='admins')need(['super']);$s=$db->prepare($map[$t]." ORDER BY id DESC LIMIT 15 OFFSET $off");}else out(['error'=>'?'],400);
 $s->execute([$q,$q]);out($s->fetchAll());}
if($a==='tx_action'){need(['super','admin']);$id=(int)$in['id'];$st=$in['do']==='approve'?'done':'rejected';$db->beginTransaction();
 $s=$db->prepare("SELECT * FROM transactions WHERE id=? AND status='pending' FOR UPDATE");$s->execute([$id]);$t=$s->fetch();if(!$t){$db->rollBack();out(['error'=>'Không hợp lệ'],400);}
 if($st==='done'){$d=$t['type']==='deposit'?$t['amount']-$t['fee']:-$t['amount'];
  $u=$db->prepare('UPDATE players SET balance=balance+? WHERE id=? AND balance+?>=0');$u->execute([$d,$t['player_id'],$d]);if(!$u->rowCount()){$db->rollBack();out(['error'=>'Số dư không đủ'],400);}}
 $db->prepare('UPDATE transactions SET status=? WHERE id=?')->execute([$st,$id]);$db->commit();logit("Giao dịch #$id: $st");out(['ok'=>1]);}
if($a==='player_lock'){need(['super','admin']);$db->prepare("UPDATE players SET status=IF(status='active','locked','active') WHERE id=?")->execute([(int)$in['id']]);logit('Khóa/mở người chơi',$in['id']);out(['ok'=>1]);}
if($a==='player_adjust'){need(['super','admin']);$db->prepare('UPDATE players SET balance=GREATEST(0,balance+?) WHERE id=?')->execute([(int)$in['amount'],(int)$in['id']]);logit('Điều chỉnh số dư',"#{$in['id']} {$in['amount']} lý do: ".($in['note']??''));out(['ok'=>1]);}
if($a==='table_save'){need(['super','admin']);if(!empty($in['id']))$db->prepare('UPDATE tables_game SET code=?,name=? WHERE id=?')->execute([$in['code'],$in['name'],$in['id']]);else $db->prepare('INSERT INTO tables_game(code,name)VALUES(?,?)')->execute([$in['code'],$in['name']]);logit('Lưu bàn',$in['name']);out(['ok'=>1]);}
if($a==='table_del'){need(['super']);$db->prepare('DELETE FROM tables_game WHERE id=?')->execute([(int)$in['id']]);logit('Xóa bàn',$in['id']);out(['ok'=>1]);}
if($a==='setting'){need(['super']);$db->prepare('REPLACE INTO settings VALUES(?,?)')->execute([$in['k'],$in['v']]);logit('Đổi cài đặt',"{$in['k']}={$in['v']}");out(['ok'=>1]);}
if($a==='admin_add'){need(['super']);$db->prepare('INSERT INTO admins(username,password_hash,role)VALUES(?,?,?)')->execute([$in['u'],password_hash($in['p'],PASSWORD_DEFAULT),$in['role']]);logit('Thêm admin',$in['u']);out(['ok'=>1]);}
out(['error'=>'?'],404);
