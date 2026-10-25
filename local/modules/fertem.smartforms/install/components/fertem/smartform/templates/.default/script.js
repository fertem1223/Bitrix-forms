(function(w){
  'use strict';
  w.FertemSmartForms={init:function(root){
    if(!root||root.dataset.ready)return; root.dataset.ready='1';
    const form=root.querySelector('form'), id=root.dataset.formId, key='fertem-smartform-draft-'+id;
    const query=new URLSearchParams(location.search); form.elements._page.value=location.href;
    ['utm_source','utm_medium','utm_campaign'].forEach(k=>form.elements['_'+k].value=query.get(k)||'');
    try{const saved=JSON.parse(localStorage.getItem(key)||'{}');Object.keys(saved).forEach(k=>{if(form.elements[k]&&!k.startsWith('_'))form.elements[k].value=saved[k]})}catch(e){}
    const conditions=()=>root.querySelectorAll('[data-condition-field]').forEach(box=>{const source=form.elements[box.dataset.conditionField];const show=source&&(source.type==='checkbox'?(source.checked?'Y':''):source.value)===box.dataset.conditionValue;box.hidden=!show;box.querySelectorAll('input,textarea,select').forEach(el=>el.disabled=!show)});
    form.addEventListener('input',()=>{conditions();const data=Object.fromEntries(new FormData(form));delete data.website;try{localStorage.setItem(key,JSON.stringify(data))}catch(e){}}); conditions();
    form.addEventListener('submit',function(e){e.preventDefault();root.querySelectorAll('[data-error]').forEach(x=>x.textContent='');const btn=root.querySelector('[type=submit]'),msg=root.querySelector('[role=status]');btn.disabled=true;msg.textContent='Отправляем…';
      const fields=Object.fromEntries(new FormData(form));
      BX.ajax.runComponentAction('fertem:smartform','submit',{mode:'class',data:{fields:fields},signedParameters:root.dataset.signedParameters}).then(r=>{const d=r.data;if(d.ok){msg.className='fertem-smartform__message --ok';msg.textContent=d.message;localStorage.removeItem(key);form.reset()}else{msg.textContent=Array.isArray(d.errors)?d.errors.join(' '):'Проверьте поля.';if(d.errors&&!Array.isArray(d.errors))Object.keys(d.errors).forEach(k=>{const el=root.querySelector('[data-error="'+CSS.escape(k)+'"]');if(el)el.textContent=d.errors[k]})}}).catch(()=>{msg.textContent='Не удалось отправить форму. Попробуйте ещё раз.'}).finally(()=>btn.disabled=false);
    });
  }};
  document.addEventListener('DOMContentLoaded',()=>document.querySelectorAll('.fertem-smartform').forEach(w.FertemSmartForms.init));
})(window);

