import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-nova-video-field', IndexField)
  app.component('detail-nova-video-field', DetailField)
  app.component('form-nova-video-field', FormField)
})
